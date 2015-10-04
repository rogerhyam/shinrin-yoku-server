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
        LEFT JOIN au_osm_reverse_geocode as a ON a.submission_id = s.id
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
    
    $address_json =  get_osm_data($survey->geolocation->longitude,$survey->geolocation->latitude);
    $address = json_decode($address_json);
    
    save_address_data($address_json, $address->address->country_code, $submission_id);
}

function save_address_data($raw, $country_code, $submission_id){
    
    global $mysqli;
    
    $json_data = $mysqli->real_escape_string($raw);
    $sql = "INSERT INTO `au_osm_reverse_geocode` (`submission_id`, `raw`, `country_code`)
            VALUES ($submission_id, '$json_data', '$country_code')
            ON DUPLICATE KEY UPDATE `raw` = '$json_data', `country_code` = '$country_code';
    ";
    $mysqli->query($sql);
    error_log($mysqli->error);
    
}

function get_osm_data($lon, $lat){
     $url = "http://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lon&zoom=18&addressdetails=1&email=r.hyam@rbge.org.uk";
     $response = file_get_contents($url);
     return $response; 
}



?>