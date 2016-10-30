<!--Telegraaf zoekmachine
Richard Olij (10833730) & Melissa Tjhia (10761071)

This is the main file, queries are executed based on the query terms and the facets/filters. The results are processed in textual-elements.php, the object that are needed in the html are then returned and positioned here.
-->

<?
    require "textual-elements.php"; 
    require "queries.php";
    require "../phpmyadmin/vendor/autoload.php";

    // Get basic information out of querystring
    $query = $_GET['query']; 
    $from = $_GET['from']; 
    if(!$from){
        $from = 0;
    }
    $nrresults = $_GET['results']; 
    if(!$nrresults){
        $nrresults = 10;
    }

    // If from submit, or if a query was given in querystring, start searching
    if (isset($_POST['query_terms']) || $query) {

        // If queryterms were given from the submit, use that. 
        // If not, use the querystring
        $query_terms = $_POST['query_terms'];
        if(!$query_terms){
            $query_terms = $query;
        }
        
        // If a date is set in the querystring, parse and save for the page.
        $date = $_GET['date'];
        if($date){
            $_POST['year'] = [substr($date,0,4)];
            $_POST['month'] = [substr($date,5,2)];
            $_POST['day'] = [substr($date,8,2)];
        }
        
        // Id no type is set, set the default of all
        $type = $_POST['type'];
        if(!$type){
            $type = 'all';
        }
        
        // Check if there was a type in the querystring, if so, use this.
        $q_type = $_GET['type'];
        if($q_type){
            $_POST['type'] =$q_type;
            $type = $q_type;
        }
        
        // Get the data from advanced search.
        $years = $_POST['year'];
        $months = $_POST['month'];
        $days = $_POST['day'];
        $fields = $_POST['field'];
        $sort = $_POST['sort'];
        
        // Get the data given the parameters.
        $params = getUserInputQuery($query_terms,$years,$months,$days,$fields,$sort,$type);
        $data = getQueryResult($params);
        $results = summarizeQueryResults($data, $query_terms,$from,$nrresults);
        
        // Store the results per variable instead of the list.
        $scores = $results[0];
        $htmlResults = $results[1];
        $textCounts = $results[2];
        $number_hits_per_subject = $results[3];
        $hits_over_years = $results[4];
    }
?>

<html>
    <head>
        <title>Telegraaf zoekmachine</title>
        <link rel="stylesheet" type="text/css" href="style.css"/>
        
        <!-- Barcharts -->
        <link rel="stylesheet" type="text/css" href="barchart.css"/>
        <script src="https://d3js.org/d3.v3.min.js" charset="utf-8"></script>
        <script src="http://labratrevenge.com/d3-tip/javascripts/d3.tip.v0.6.3.js"></script>
        <script type="text/javascript" src="barchart.js"></script>
        
        <!-- Collapse -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        
        <!-- Cloud -->
        <script src="d3.layout.cloud.js"></script>
        <script src="cloud.js"></script>
    </head>
    
    <body>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <div id="dtop" >
            
            <div id="dtopright">
                <!--Words Cloud-->
                <script>
                    <? echo "var frequency_list = ".$textCounts.";"; ?>
                    if(frequency_list.length>0){
                        initializeCould(frequency_list);
                    }
                </script>
            </div>
            
            <!-- title and search -->
            <div id="dtopleft" >
            
                <div id="title">
                    <center>
                        <? 
                            $url = $_SERVER['PHP_SELF'].'?'.http_build_query(array('query'=>'Ik wil dit +wel maar dit -niet zoeken vanaf resultaat 5 met 15 resultaten','from'=>5,'results'=>15));
                        ?>
                        <a href="<?php echo $url; ?>"><h1>Telegraaf zoekmachine </h1></a>
                        </br >
                        Melissa Tjhia (10761071) & Richard Olij (10833730)
                    </center>
                </div>
                
                <div id="search">
                    <!-- Input for query terms -->
                    
                    <? 
                        $query = $_GET['query']; 
                        if(! $query){
                            $query = '';
                        }
                    ?>
                    
                    <br>Zoeken naar: <input type="text" name="query_terms" id="search_bar" value="<?php echo isset($_POST['query_terms']) ? $_POST['query_terms'] : $query ?>">
                    <br>
                    <br>
                    <input type="submit">
                </div>
            </div>
        </div>
        <!-- Below are all the facets, results and graphs -->
        <div id="dbottom" >
            <div id="dbottomleft" >

                <div class="dform" style="min-width:60px">
                    <a class="formtitle" data-toggle="collapse" data-target="#dcbsYear">Jaar</a><br>
                    <div id="dcbsYear" class="collapse">
                        <input type="checkbox" name="year[]" id="year" value="1918" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1918', $_POST['year'])) echo 'checked="checked"'; ?> > 1918<br>
                        <input type="checkbox" name="year[]" id="year" value="1922" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1922', $_POST['year'])) echo 'checked="checked"'; ?> > 1922<br>
                        <input type="checkbox" name="year[]" id="year" value="1923" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1923', $_POST['year'])) echo 'checked="checked"'; ?> > 1923<br>
                        <input type="checkbox" name="year[]" id="year" value="1951" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1951', $_POST['year'])) echo 'checked="checked"'; ?> > 1951<br>
                        <input type="checkbox" name="year[]" id="year" value="1961" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1961', $_POST['year'])) echo 'checked="checked"'; ?> > 1961<br>
                        <input type="checkbox" name="year[]" id="year" value="1962" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1962', $_POST['year'])) echo 'checked="checked"'; ?> > 1962<br>
                        <input type="checkbox" name="year[]" id="year" value="1963" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1963', $_POST['year'])) echo 'checked="checked"'; ?> > 1963<br>
                        <input type="checkbox" name="year[]" id="year" value="1965" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1965', $_POST['year'])) echo 'checked="checked"'; ?> > 1965<br>
                        <input type="checkbox" name="year[]" id="year" value="1966" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1966', $_POST['year'])) echo 'checked="checked"'; ?> > 1966<br>
                        <input type="checkbox" name="year[]" id="year" value="1968" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1968', $_POST['year'])) echo 'checked="checked"'; ?> > 1968<br>
                        <input type="checkbox" name="year[]" id="year" value="1969" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1969', $_POST['year'])) echo 'checked="checked"'; ?> > 1969<br>
                        <input type="checkbox" name="year[]" id="year" value="1970" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1970', $_POST['year'])) echo 'checked="checked"'; ?> > 1970<br>
                        <input type="checkbox" name="year[]" id="year" value="1971" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1971', $_POST['year'])) echo 'checked="checked"'; ?> > 1971<br>
                        <input type="checkbox" name="year[]" id="year" value="1972" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1972', $_POST['year'])) echo 'checked="checked"'; ?> > 1972<br>
                        <input type="checkbox" name="year[]" id="year" value="1973" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1973', $_POST['year'])) echo 'checked="checked"'; ?> > 1973<br>
                        <input type="checkbox" name="year[]" id="year" value="1974" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1974', $_POST['year'])) echo 'checked="checked"'; ?> > 1974<br>
                        <input type="checkbox" name="year[]" id="year" value="1975" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1975', $_POST['year'])) echo 'checked="checked"'; ?> > 1975<br>
                        <input type="checkbox" name="year[]" id="year" value="1976" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1976', $_POST['year'])) echo 'checked="checked"'; ?> > 1976<br>
                        <input type="checkbox" name="year[]" id="year" value="1977" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1977', $_POST['year'])) echo 'checked="checked"'; ?> > 1977<br>
                        <input type="checkbox" name="year[]" id="year" value="1978" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1978', $_POST['year'])) echo 'checked="checked"'; ?> > 1978<br>
                        <input type="checkbox" name="year[]" id="year" value="1979" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1979', $_POST['year'])) echo 'checked="checked"'; ?> > 1979<br>
                        <input type="checkbox" name="year[]" id="year" value="1980" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1980', $_POST['year'])) echo 'checked="checked"'; ?> > 1980<br>
                        <input type="checkbox" name="year[]" id="year" value="1981" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1981', $_POST['year'])) echo 'checked="checked"'; ?> > 1981<br>
                        <input type="checkbox" name="year[]" id="year" value="1982" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1982', $_POST['year'])) echo 'checked="checked"'; ?> > 1982<br>
                        <input type="checkbox" name="year[]" id="year" value="1983" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1983', $_POST['year'])) echo 'checked="checked"'; ?> > 1983<br>
                        <input type="checkbox" name="year[]" id="year" value="1984" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1984', $_POST['year'])) echo 'checked="checked"'; ?> > 1984<br>
                        <input type="checkbox" name="year[]" id="year" value="1985" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1985', $_POST['year'])) echo 'checked="checked"'; ?> > 1985<br>
                        <input type="checkbox" name="year[]" id="year" value="1986" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1986', $_POST['year'])) echo 'checked="checked"'; ?> > 1986<br>
                        <input type="checkbox" name="year[]" id="year" value="1987" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1987', $_POST['year'])) echo 'checked="checked"'; ?> > 1887<br>
                        <input type="checkbox" name="year[]" id="year" value="1988" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1988', $_POST['year'])) echo 'checked="checked"'; ?> > 1988<br>
                        <input type="checkbox" name="year[]" id="year" value="1989" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1989', $_POST['year'])) echo 'checked="checked"'; ?> > 1989<br>
                        <input type="checkbox" name="year[]" id="year" value="1990" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1990', $_POST['year'])) echo 'checked="checked"'; ?> > 1990<br>
                        <input type="checkbox" name="year[]" id="year" value="1991" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1991', $_POST['year'])) echo 'checked="checked"'; ?> > 1991<br>
                        <input type="checkbox" name="year[]" id="year" value="1992" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1992', $_POST['year'])) echo 'checked="checked"'; ?> > 1992<br>
                        <input type="checkbox" name="year[]" id="year" value="1993" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1993', $_POST['year'])) echo 'checked="checked"'; ?> > 1993<br>
                        <input type="checkbox" name="year[]" id="year" value="1994" <?php if(isset($_POST['year']) && is_array($_POST['year']) && in_array('1994', $_POST['year'])) echo 'checked="checked"'; ?> > 1994<br>
                    </div>
                </div>

                <div class="dform" style="min-width:87px">
                    <a class="formtitle" data-toggle="collapse" data-target="#dcbsMonth">Maand</a><br>
                    <div id="dcbsMonth" class="collapse">
                        <input type="checkbox" name="month[]" value="01" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('01', $_POST['month'])) echo 'checked="checked"'; ?> > January<br>
                        <input type="checkbox" name="month[]" value="02" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('02', $_POST['month'])) echo 'checked="checked"'; ?> > February<br>
                        <input type="checkbox" name="month[]" value="03" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('03', $_POST['month'])) echo 'checked="checked"'; ?> > March<br>
                        <input type="checkbox" name="month[]" value="04" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('04', $_POST['month'])) echo 'checked="checked"'; ?> > April<br>
                        <input type="checkbox" name="month[]" value="05" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('05', $_POST['month'])) echo 'checked="checked"'; ?> > May<br>
                        <input type="checkbox" name="month[]" value="06" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('06', $_POST['month'])) echo 'checked="checked"'; ?> > June<br>
                        <input type="checkbox" name="month[]" value="07" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('07', $_POST['month'])) echo 'checked="checked"'; ?> > Juli<br>
                        <input type="checkbox" name="month[]" value="08" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('08', $_POST['month'])) echo 'checked="checked"'; ?> > August<br>
                        <input type="checkbox" name="month[]" value="09" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('09', $_POST['month'])) echo 'checked="checked"'; ?> > September<br>
                        <input type="checkbox" name="month[]" value="10" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('10', $_POST['month'])) echo 'checked="checked"'; ?> > October<br>
                        <input type="checkbox" name="month[]" value="11" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('11', $_POST['month'])) echo 'checked="checked"'; ?> > November<br>
                        <input type="checkbox" name="month[]" value="12" <?php if(isset($_POST['month']) && is_array($_POST['month']) && in_array('12', $_POST['month'])) echo 'checked="checked"'; ?> > December<br>
                    </div>
                </div>

                <div class="dform" style="min-width:37px">
                    <a class="formtitle" data-toggle="collapse" data-target="#dcbsDay">Dag</a><br>
                    <div id="dcbsDay" class="collapse">
                        <input type="checkbox" name="day[]" value="01" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('01', $_POST['day'])) echo 'checked="checked"'; ?> > 1<br>
                        <input type="checkbox" name="day[]" value="02" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('02', $_POST['day'])) echo 'checked="checked"'; ?> > 2<br>
                        <input type="checkbox" name="day[]" value="03" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('03', $_POST['day'])) echo 'checked="checked"'; ?> > 3<br>
                        <input type="checkbox" name="day[]" value="04" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('04', $_POST['day'])) echo 'checked="checked"'; ?> > 4<br>
                        <input type="checkbox" name="day[]" value="05" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('05', $_POST['day'])) echo 'checked="checked"'; ?> > 5<br>
                        <input type="checkbox" name="day[]" value="06" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('06', $_POST['day'])) echo 'checked="checked"'; ?> > 6<br>
                        <input type="checkbox" name="day[]" value="07" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('07', $_POST['day'])) echo 'checked="checked"'; ?> > 7<br>
                        <input type="checkbox" name="day[]" value="08" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('08', $_POST['day'])) echo 'checked="checked"'; ?> > 8<br>
                        <input type="checkbox" name="day[]" value="09" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('09', $_POST['day'])) echo 'checked="checked"'; ?> > 9<br>
                        <input type="checkbox" name="day[]" value="10" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('10', $_POST['day'])) echo 'checked="checked"'; ?> > 10<br>
                        <input type="checkbox" name="day[]" value="11" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('11', $_POST['day'])) echo 'checked="checked"'; ?> > 11<br>
                        <input type="checkbox" name="day[]" value="12" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('12', $_POST['day'])) echo 'checked="checked"'; ?> > 12<br>
                        <input type="checkbox" name="day[]" value="13" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('13', $_POST['day'])) echo 'checked="checked"'; ?> > 13<br>
                        <input type="checkbox" name="day[]" value="14" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('14', $_POST['day'])) echo 'checked="checked"'; ?> > 14<br>
                        <input type="checkbox" name="day[]" value="15" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('15', $_POST['day'])) echo 'checked="checked"'; ?> > 15<br>
                        <input type="checkbox" name="day[]" value="16" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('16', $_POST['day'])) echo 'checked="checked"'; ?> > 16<br>
                        <input type="checkbox" name="day[]" value="17" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('17', $_POST['day'])) echo 'checked="checked"'; ?> > 17<br>
                        <input type="checkbox" name="day[]" value="18" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('18', $_POST['day'])) echo 'checked="checked"'; ?> > 18<br>
                        <input type="checkbox" name="day[]" value="19" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('19', $_POST['day'])) echo 'checked="checked"'; ?> > 19<br>
                        <input type="checkbox" name="day[]" value="20" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('20', $_POST['day'])) echo 'checked="checked"'; ?> > 20<br>
                        <input type="checkbox" name="day[]" value="21" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('21', $_POST['day'])) echo 'checked="checked"'; ?> > 21<br>
                        <input type="checkbox" name="day[]" value="22" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('22', $_POST['day'])) echo 'checked="checked"'; ?> > 22<br>
                        <input type="checkbox" name="day[]" value="23" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('23', $_POST['day'])) echo 'checked="checked"'; ?> > 23<br>
                        <input type="checkbox" name="day[]" value="24" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('24', $_POST['day'])) echo 'checked="checked"'; ?> > 24<br>
                        <input type="checkbox" name="day[]" value="25" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('25', $_POST['day'])) echo 'checked="checked"'; ?> > 25<br>
                        <input type="checkbox" name="day[]" value="26" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('26', $_POST['day'])) echo 'checked="checked"'; ?> > 26<br>
                        <input type="checkbox" name="day[]" value="27" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('27', $_POST['day'])) echo 'checked="checked"'; ?> > 27<br>
                        <input type="checkbox" name="day[]" value="28" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('28', $_POST['day'])) echo 'checked="checked"'; ?> > 28<br>
                        <input type="checkbox" name="day[]" value="29" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('29', $_POST['day'])) echo 'checked="checked"'; ?> > 29<br>
                        <input type="checkbox" name="day[]" value="30" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('30', $_POST['day'])) echo 'checked="checked"'; ?> > 30<br>
                        <input type="checkbox" name="day[]" value="31" <?php if(isset($_POST['day']) && is_array($_POST['day']) && in_array('31', $_POST['day'])) echo 'checked="checked"'; ?> > 31<br>
                    </div>
                </div>

                <div class="dform" style="min-width:60px">
                    <a class="formtitle" data-toggle="collapse" data-target="#dcbsField">Velden</a><br>
                    <div id="dcbsField" class="collapse">

                        <input type="checkbox" name="field[]" value="title" <?php if(isset($_POST['field']) && is_array($_POST['field']) && in_array('title', $_POST['field'])) echo 'checked="checked"'; ?> > Titel<br>

                        <input type="checkbox" name="field[]" value="text" <?php if(isset($_POST['field']) && is_array($_POST['field']) && in_array('text', $_POST['field'])) echo 'checked="checked"'; ?> > Tekst<br>
                    </div>
                </div>

                <div class="dform" style="min-width:60px">
                    <a class="formtitle" data-toggle="collapse" data-target="#dcbsSort">Sorteren</a><br>
                    <div id="dcbsSort" class="collapse">

                        <input type="radio" name="sort" value="score" <?php if(!isset($_POST['sort']) || $_POST['sort'] == 'score') echo 'checked="checked"'; ?> > Score<br>

                        <input type="radio" name="sort" value="asc" <?php if(isset($_POST['sort']) && $_POST['sort'] == 'asc') echo 'checked="checked"'; ?> > Nieuwste datum<br>

                        <input type="radio" name="sort" value="desc" <?php if(isset($_POST['sort']) && $_POST['sort'] == 'desc') echo 'checked="checked"'; ?> > Oudste datum<br>
                    </div>
                </div>
                
                <div class="dform" style="min-width:70px">
                    <a class="formtitle" data-toggle="collapse" data-target="#dcbsTypes">Type</a><br>
                    <div id="dcbsTypes" class="collapse">

                        <input type="radio" name="type" value="all" <?php if(!isset($_POST['type']) || $_POST['type'] == 'all') echo 'checked="checked"'; ?> > alles<br>
                        
                        <input type="radio" name="type" value="artikel" <?php if(isset($_POST['type']) && $_POST['type'] == 'artikel') echo 'checked="checked"'; ?> > artikel<br>
                        
                        <input type="radio" name="type" value="illustratie met onderschrift" <?php if(isset($_POST['type']) && $_POST['type'] == 'illustratie met onderschrift') echo 'checked="checked"'; ?> > illustratie<br>
                        
                        <input type="radio" name="type" value="advertentie" <?php if(isset($_POST['type']) && $_POST['type'] == 'advertentie') echo 'checked="checked"'; ?> > advertentie<br>
                        
                        <input type="radio" name="type" value="familiebericht" <?php if(isset($_POST['type']) && $_POST['type'] == 'familiebericht') echo 'checked="checked"'; ?> > familiebericht<br>  
                    </div>
                </div>


                <!-- Score chart -->
                <?  
                    if ($scores) { 
                        echo '<div id="dtopsvg" >';
                        echo "<div id='dscores'>";
                        echo "<h3><center>Scores</center></h3>"; 
                    }
                ?>
                <script>
                // Send the score from php to javascript
                    <? echo "var data_scores = ".json_encode($scores).";"; ?>
                    if (data_scores) { 
                        displayChart(reformatData(data_scores),"#dscores");
                    }
                </script>
                <?  
                    if ($scores) { 
                        echo '</div>';  
                    }
                ?>

                <!-- Result per chart -->
                <?  
                    if ($number_hits_per_subject) { 
                        echo "<div id='dsoort'>";
                        echo "<h3><center>Resultaten per soort</center></h3>"; 
                    }
                ?>
                <script>
                // Send the score from php to javascript
                    <? echo "var n_hits = ".json_encode($number_hits_per_subject).";"; ?>
                    if (n_hits) { 
                        displayChart(n_hits,"#dsoort");
                    }
                </script>
                <?  
                    if ($number_hits_per_subject) { 
                        echo '</div>';  
                    }
                ?>

                <!-- Tijdlijn chart -->
                <?  
                    if ($hits_over_years) { 
                        echo "<div id='dtijdlijn'>";
                        echo "<h3><center>Tijdlijn</center></h3>"; 
                    }
                ?>
                <script>
                // Send the score from php to javascript
                    <? echo "var h_y = ".json_encode($hits_over_years).";"; ?>
                    if (h_y) { 
                        displayChart(h_y,"#dtijdlijn");
                    }
                </script>
                <?  
                    if ($hits_over_years) { 
                        echo '</div>';  
                    }
                ?> 

            </div>

        </div>
            
        <div id="dresults" >
            <? echo $htmlResults; ?>
            <br>
            <center>
                <?
                    if($scores){ // if there were results.

                        // If the nr of results is less than 1, give default 10
                        if($nrresults<1){
                            $nrresults = 10;
                        }

                        // For the 'previous' button max amount of previous results 
                        // can be les or equal to the current $from value
                        $nrresults_prev = $nrresults;
                        if($nrresults_prev>$from){
                            $nrresults_prev = $from;
                         }

                        // The form can only be positive
                        $new_from = $from-$nrresults_prev;
                        if($new_from<0){
                            $new_from = 0;
                        }

                        // If there are no previous results, dont show the button.
                        if($nrresults_prev > 0){
                             $url = $_SERVER['PHP_SELF'].'?'.http_build_query(array('query'=>$query_terms,'from'=>$new_from,'results'=>$nrresults_prev));
                            echo '<a href="'.$url.'">vorige '.$nrresults_prev.'  </a>';
                        }
                        
                        // Show the button for next results.
                        $url = $_SERVER['PHP_SELF'].'?'.http_build_query(array('query'=>$query_terms,'from'=>$from+$nrresults,'results'=>$nrresults));
                        echo '<a href="'.$url.'">  volgende '.$nrresults.'</a>';
                    }
                ?>
            </center>
        </div>
    </form>
    </body>
</html>