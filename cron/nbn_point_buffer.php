<?php

include_once('../config.php');
include_once('utils.php');

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

    $buffer_wkt = get_buffer_polygone_for_point_wkt($survey->geolocation->longitude,$survey->geolocation->latitude,500);
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
    $url = "https://data.nbn.org.uk/api/taxonObservations/species?absence=false&spatialRelationship=within&polygon=" . urlencode($wkt);
    $json = file_get_contents($url);
    echo $wkt;
    echo "<hr />\n";
    echo $url;
    return $json;
}




?>