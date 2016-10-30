<!--
Richard Olij (10833730) & Melissa Tjhia (10761071)

This file processes turns the user input and selected filters into a query
-->

<?    
require '../phpmyadmin/vendor/autoload.php';
use Elasticsearch\ClientBuilder;

// here the variables from the user will be processed
function getUserInputQuery($query_terms, $years, $months, $days, $fields, $sort, $type){
    // The prefixes for a word must or must not be contained.
    $NOT = "-";
    $MUST = "+";
    
    // An arran of all should, must and must_not's will be filled while looping through all query-terms
    $a_should = array();
    $a_must = array();
    $a_not = array();     
    $a_words = explode(' ', $query_terms);
    foreach ($a_words as $word){
        // if the first part of the word is an prefix, 
        //then clean the word and put in in the correspronding array
        
        if (substr($word, 0, strlen($NOT)) == $NOT){
            $cleanWord = substr($word, strlen($NOT), strlen($word));
            array_push($a_not, $cleanWord);
        }
        else if (substr($word, 0, strlen($MUST)) == $MUST){
            $cleanWord = substr($word, strlen($MUST), strlen($word));
            array_push($a_must, $cleanWord);
        }
        else{
            array_push($a_should, $word);
        }
    }
    
    // Create the correct parameters and return ($params by reference)
    if (count($fields) > 0) {
        addToQuery($params,$a_should, "should", $fields);
        addToQuery($params,$a_must, "must", $fields);
        addToQuery($params,$a_not, "must_not", $fields);
    } else {
        addToQuery($params,$a_should, "should", ["title", "text"]);
        addToQuery($params,$a_must, "must", ["text"]);
        addToQuery($params,$a_not, "must_not", ["title", "text"]);
    }
    
    // If all values for years, months and days are filled, then use this informatin
    // to create all possible combinations of those so filter on.
    if (count($years) > 0 and count($months) > 0 and count($days) > 0){
        $dates = array();
        foreach ($years as $year){
            foreach ($months as $month){
                foreach ($days as $day){
                    // Put it in the right format.
                    $s = sprintf("%s-%s-%s", $year, $month, $day);
                    array_push($dates, $s);
                }
            } 
        }
        addToFilter($params, $dates, "or", ["date"]);
    }
    
    // If sort is set, use this to crate the sort parameter
    if ($sort) {
        addToOrder($params, $sort);
    }
    
    // Only when a specific type is selected.
    if($type!='all'){
        $params['type'] = $type;
    }
    return $params;
}

function getQueryResult($params){
    // For the highlighting of query terms
    $params['body']['highlight']['fields']['text']['fragment_size'] = 150;
    $params['body']['highlight']['fields']['text']['number_of_fragments'] = 3;
    $params['body']['highlight']['fields']['text']['no_match_size'] = 150;
    $params['body']['highlight']['fields']['text']['pre_tags'] = ["<b>"];
    $params['body']['highlight']['fields']['text']['post_tags'] = ["</b>"];
    
    $params['body']['from'] = 0;
    $params['body']['size'] = 10000; // The amount of data used to create the word-cloud and certain graphs

    $client = ClientBuilder::create()->build();
    $data = $client->search($params);

    return $data;
}
    
    
// $a_words is a single word
// $a_fields is an array of parts where it should be found, e.g. title or body
// $prop is either must, must_not, should or filter
function addToQuery(&$params, $a_words, $prop, $a_fields){
    $a_p = [];
    foreach ($a_words as $word){
        foreach ($a_fields as $field){
            $p = [];
            $p['term'][$field] = $word;
            array_push($a_p, $p);
        }
    }
    
    $params['body']['query']['bool'][$prop] = $a_p;    
}

// $a_words is a single word
// $a_fields is an array of parts where it should be found, e.g. title or body
// $prop is either or, and
function addToFilter(&$params, $a_words, $prop, $a_fields){
    $a_p = [];
    foreach ($a_words as $word){
        foreach ($a_fields as $field){
            $p = [];
            $p['term'][$field] = $word;
            array_push($a_p, $p);
        }
    }
    
    $params['body']['query']['bool']['filter'][$prop]['filters'] = $a_p;    
}

// $order can either be "desc" or "asc"
function addToOrder(&$params, $order){
    if($order != "score"){
        $params['body']['sort']['date']['order'] = $order;
    }
}

?>