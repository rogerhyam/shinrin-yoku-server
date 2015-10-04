<?php

include_once('../config.php');

// if the survey id is set the just process that one
// otherwise work through the next 10 that don't have results
if(isset($_GET['survey'])){
    
    $survey_id = $_GET['survey'];
    if(count($survey_id) > 50) exit; // fixme: quick injection check
    
    $sql = "SELECT survey_json, id as submission_id FROM submissions WHERE survey_id = '$survey_id'";
    $response = $mysqli->query($sql);
    $row = $response->fetch_assoc();
    $survey = json_decode($row['survey_json']);
    $submission_id = $row['submission_id'];
    
    process_survey($survey, $submission_id);
        
}else{
    
    $sql = "SELECT s.survey_json, s.id as submission_id, a.id
        FROM submissions as s
        LEFT JOIN au_nbn_point_buffer as a ON a.submission_id = s.id
        WHERE a.id IS NULL
        ORDER by a.modified
        limit 10";
    
    $response = $mysqli->query($sql);
    while($row = $response->fetch_assoc()){
        $survey = json_decode($row['survey_json']);
        $submission_id = $row['submission_id'];
        process_survey($survey, $submission_id);
        echo "done $submission_id \n";
    }
    
}


function process_survey($survey, $submission_id){
    
    global $mysqli;

    $buffer_wkt = get_buffer_polygone_for_point($survey->geolocation->longitude,$survey->geolocation->latitude,100);
    $nbn_response_json = get_nbn_species_for_polygon($buffer_wkt);
    $nbn_response =  json_decode($nbn_response_json);
    
    // response is an array of objects containing querySpecificObservationCount and taxon
    // work out some totals so we can search on them
    $totals = new stdClass();
    $totals->observationCount = 0;
    $totals->taxonCount = 0;
    foreach($nbn_response as $occurrance){
        $taxon = $occurrance->taxon;
        $totals->taxonCount ++;
        $totals->observationCount += $occurrance->querySpecificObservationCount;
    }
    
    //echo $nbn_response_json;
    
    // add it to the db
    $json_data = $mysqli->real_escape_string($nbn_response_json);
    $sql = "INSERT INTO `au_nbn_point_buffer` (`submission_id`, `raw`, `taxon_count`, `observation_count`)
            VALUES ($submission_id, '$json_data', $totals->taxonCount, $totals->observationCount)
            ON DUPLICATE KEY UPDATE `raw` = '$json_data', `taxon_count` = $totals->taxonCount, `observation_count` = $totals->observationCount;
    ";
    $mysqli->query($sql);
   // error_log($mysqli->error);
    
}

function get_nbn_species_for_polygon($wkt){
    $url = "https://data.nbn.org.uk/api/taxonObservations/species?polygon=" . urlencode($wkt);
    //$json = CurlGetString($url);
    $json = file_get_contents($url);
    //echo $url;
    return $json;
}

function CurlGetString($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/* 
    given a point and a distance produce a WKT polygone that approximates a circle
*/
function get_buffer_polygone_for_point($lon,$lat,$d_metres){
 
 $points = array();
 for($i = 0; $i < 360; $i = $i + 10){
     $points[] = get_point_for_distance($lon,$lat,$d_metres / 1000,$i);
 }
 
 // print_r($points);
 
 $out = "POLYGON((";
 for($i = 0; $i < count($points); $i++){
     if($i > 0) $out .= ', ';
     $out .= implode(' ', $points[$i]);
 }
 $out .= ', ' . implode(' ', $points[0]); // ends with start point
 $out .= "))";
 
 return $out;
 
}

function get_point_for_distance($long1,$lat1,$d,$angle){
    # Earth Radious in KM
    $R = 6378.14;

    # Degree to Radian
    $latitude1 = $lat1 * (M_PI/180);
    $longitude1 = $long1 * (M_PI/180);
    $brng = $angle * (M_PI/180);

    $latitude2 = asin(sin($latitude1)*cos($d/$R) + cos($latitude1)*sin($d/$R)*cos($brng));
    $longitude2 = $longitude1 + atan2(sin($brng)*sin($d/$R)*cos($latitude1),cos($d/$R)-sin($latitude1)*sin($latitude2));

    # back to degrees
    $latitude2 = $latitude2 * (180/M_PI);
    $longitude2 = $longitude2 * (180/M_PI);

    # 6 decimal for Leaflet and other system compatibility
   $lat2 = round ($latitude2,6);
   $long2 = round ($longitude2,6);

   // Push in array and get back
   $tab[0] = $long2;
   $tab[1] = $lat2;
   return $tab;
}

?>