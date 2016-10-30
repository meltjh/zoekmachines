<!--
Richard Olij (10833730) & Melissa Tjhia (10761071)

This file processes the results from the query, such as the top results with the 
title, date, type of document and a summary. It also processes the data for the charts and word cloud. 
-->

<?
// Get all the textual elements for the result page
function summarizeQueryResults($data, $query_terms, $from, $nrresults) {
    $html = "";
    
    $took = $data["took"];
    $timed_out = $data["timed_out"];

    $_shards = $data["_shards"];
        $_shards_total = $_shards["total"];
        $_shards_successful = $_shards["successful"];
        $_shards_failed = $_shards["failed"];

    $hits = $data["hits"];
        $hits_total = $hits["total"];
        $hits_max_score = $hits["max_score"];

    $scores = array();
        $all_hits_hits = $hits["hits"]; // All results
        $hits_all_results = getDataFromAllResults($all_hits_hits);
        $hits_per_subject_json = $hits_all_results[0]; // For barchart with documents per subject
        $hits_over_years_json = $hits_all_results[1]; // For timeline
        $allText = $hits_all_results[2]; // For word cloud
    
        $hits_hits = array_slice($all_hits_hits, $from, $nrresults); // Top results to show
        
        $num = $from + 1; // Shows result number, e.g. can begin at 10
    
        foreach ($hits_hits as $hits_hits_ ) {
            // Data from single result
            $hits_hits_index = $hits_hits_["_index"];
            $hits_hits_type = $hits_hits_["_type"];
            $hits_hits_id = $hits_hits_["_id"];
            $hits_hits_score = $hits_hits_["_score"];

            $hits_hits_source = $hits_hits_["_source"];
                $hits_hits_source_title = $hits_hits_source["title"];
                $hits_hits_source_text = $hits_hits_source["text"];
                $hits_hits_source_date = $hits_hits_source["date"];
            
            // Summary of the text, highlighted in case query terms occurs in text
            $summary_fragments = $hits_hits_["highlight"]["text"];
            $summary = "";

            if ($summary_fragments) {
                foreach ($summary_fragments as $fragment) {
                    $summary = $summary . $fragment;
                }
            }
            $url = sprintf("http://kranten.kb.nl/view/article/id/%s",$hits_hits_id);
            
            $html = $html . prettyPrint($hits_hits_source_title, $summary, $hits_hits_source_date, $url, $num, $hits_hits_type,$query_terms);

            $num += 1;
            array_push($scores, [$hits_hits_source_title, $hits_hits_score]);
        }
    
    // Top words for the word cloud
    $count_all_text = formatTextCounts($allText, $query_terms);
    
    return array($scores, $html, $count_all_text, $hits_per_subject_json, $hits_over_years_json);
}

// Returns a JSON object with words that should be in the word cloud
function formatTextCounts($allText, $query_terms){
    $allText = strtolower($allText);
    
    $TOP_CLOUD = 25; // Number of words in word cloud
    
    $count_all_text = array_count_values(str_word_count($allText, 1));
    arsort($count_all_text); // Sort it
    
    // Remove all words with small length
    $MIN_WORD_LENGTH = 1;
    foreach (array_keys($count_all_text) as $single){
        if (strlen($single) <= $MIN_WORD_LENGTH) {
            unset($count_all_text[$single]);
        }
    } 
    
    // Stopwords that should be excluded from word cloud
    $STOPWORDS = ['de','het','een', 'dat', 'deze', 'dit','die','is','en','te', 'van', 'naar', 'in', 'voor', 'had', 'op', 'niet', 'wel', 'uit', 'als', 'zijn', 'was', 'ook', 'heeft', 'hij', 'zij', 'onze', 'geworden', 'we', 'geen', 'door', 'onder', 'nog', 'hebben', 'aan', 'hem', 'haar', 'met', 'dan', 'als', 'tot', 'werd', 'al', 'over', 'je', 'jouw', 'na', 'bij', 'werd', 'er', 'om', 'af', 'hun', 'dag', 'veel', 'maar', 'kan', 'volgens', 'vindt', 'meer', 'dus', 'aldus', '-jarige', 'zich', 'waren', 'nu', 'ze', 'wordt', 'gaan', 'of', 'echter', 'worden', 'uur', 'ons', 'mij', 'per', 'ik', 'mijn', 'weer', 'wat', 'tegen', 'eigenlijk', 'wil', 'jaar', 'me', 'toch', 'tussen', 'den', 'net', 'zou', 'werden', 'daar', 'zo', 'wij', 'ter', 'ten', 'gaat', 'heb', 'ben', 'men', 'zal', 'der', 'zei'];
    
    $arr_query_terms = explode(" ", $query_terms);
    
    // Query terms should be excluded from word cloud, thus added to stop words
    foreach ($arr_query_terms as $term) {
        $first_char = substr($term, 0, 1);
        // In case the operators - and + are used in the query, remove them
        if ($first_char == '-' || $first_char == '+') {
          $term = substr($term, 1, strlen($term));  
        }
        $lower_term = strtolower($term);
        array_push($STOPWORDS, $lower_term);
    }
    
    // Remove stop words from list with word counts
    foreach ($STOPWORDS as $stopword){
        unset($count_all_text[$stopword]);
    }
    
    // Only get a top
    $count_all_text = array_slice($count_all_text, 0, $TOP_CLOUD);
    
    $json = addToJSON($count_all_text, "text", "size");

    return json_encode($json);
}

// For the barchart with results per subject, the timeline and the wordcloud
function getDataFromAllResults($all_hits_hits) {
    $allText = "";
    $subjects = [];
    $years = [];
    $i == 0;
    $MAX_CLOUD = 150; // Maximum documents that contribute to the cloud, otherwise the string will be to large.
    
    foreach ($all_hits_hits as $one_hit) {
        $hits_hits_source = $one_hit["_source"];
        
        // Append all text and titles from the top documents
        if ($i < $MAX_CLOUD) {
            $allText = $allText . $hits_hits_source["text"];
            $allText = $allText . $hits_hits_source["title"];
        }
        $i++;
        
        $subject = $one_hit["_type"]; // Type of document
        $datestr = $one_hit["_source"]["date"]; // Publishing date
        $year = substr($datestr, 0, 4); // Strip date to the year
        array_push($subjects, $subject);
        array_push($years, $year);
    }
    
    // Count occurences in the arrays
    $number_hits_per_subject = array_count_values($subjects);
    $number_hits_per_year = array_count_values($years);
    
    // Chronological order
    ksort($number_hits_per_year);
    
    $hits_prev_year = 0;
    foreach ($number_hits_per_year as $year=>$val) {
        // Found hits in current year = Found hits previous years + found hits current year
        $new_val = $val + $hits_prev_year;
        $number_hits_per_year[$year] = $new_val;
        $hits_prev_year = $new_val;
    }
    
    $number_hits_per_subject_json = addToJSON($number_hits_per_subject, "title", "score");
    $number_hits_per_year_json = addToJSON($number_hits_per_year, "title", "score");
    
    return array($number_hits_per_subject_json, $number_hits_per_year_json, $allText);
}

// Reformat into Json file  [{text:"",size:#},...]
function addToJSON($array, $title, $score) {
    $json = array();
    
    foreach (array_keys($array) as $key) {
        $single_json = array($title => $key, $score => $array[$key]);
        array_push($json, $single_json);
    }
    return $json;
}

// Return each of the results in a separate div
function prettyPrint($hits_hits_source_title, $summary, $hits_hits_source_date, $url, $num, $hits_hits_type,$query_terms) {
    preg_match('/^[\w]+$/', $summary);
    preg_match('/^[\w]+$/', $hits_hits_source_title);
    
    $html = $html."<div class='single_result'>";
    $html = $html.sprintf("<div class='title'><br><h2><a href='%s' target='_blank'>%s. %s</a></h2></div>", $url, $num, $hits_hits_source_title);
    
    $url_type = $_SERVER['PHP_SELF'].'?'.http_build_query(array('query'=>$query_terms,'type'=>$hits_hits_type));
    $url_date = $_SERVER['PHP_SELF'].'?'.http_build_query(array('query'=>$query_terms,'date'=>$hits_hits_source_date));
    
    $html = $html.sprintf("<div class='date'><a href=%s>%s</a>   <a href=%s>%s</a></div>", $url_date,$hits_hits_source_date,$url_type,$hits_hits_type);
    
    // Not all results contain a summary
    if ($summary) {
        $html = $html.sprintf("<div class='text'>%s</div>", $summary);
    }
    
    $html = $html. "</div>";
    return $html;
}

?>