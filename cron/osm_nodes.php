<?php

include_once('../config.php');
include('utils.php');
   
// if the survey id is set the just process that one
// otherwise work through the next 10 that don't have results
if(isset($_GET['survey'])){
    
    $survey_id = $_GET['survey'];
    if(count($survey_id) > 50) exit;
    
    $sql = "SELECT survey_json, id as submission_id FROM submissions WHERE survey_id = '$survey_id'";
    $response = $mysqli->query($sql);
    $row = $response->fetch_assoc();
    $survey = json_decode($row['survey_json']);
    $submission_id = $row['submission_id'];
    
    process_survey($survey, $submission_id);
        
}else{
    
    $sql = "SELECT s.survey_json, s.id as submission_id, a.id
        FROM submissions as s
        LEFT JOIN au_osm_nodes as a ON a.submission_id = s.id
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
   
    $poly = get_buffer_polygone_for_point_ql($survey->geolocation->longitude,$survey->geolocation->latitude,100);
    $url_base = "http://overpass.osm.rambler.ru/cgi/interpreter?data=";
    $query = urlencode("[out:json];(node$poly;<<;);out;");
    $json = file_get_contents($url_base . $query);
    
    
    $json_data = $mysqli->real_escape_string($json);
    $sql = "INSERT INTO `au_osm_nodes` (`submission_id`, `raw`)
            VALUES ($submission_id, '$json_data')
            ON DUPLICATE KEY UPDATE `raw` = '$json_data';
    ";
    $mysqli->query($sql);
    
    error_log($mysqli->error);
    
    if(isset($_GET['print'])){
        include_once('../lib/osm_nodes_tag_cloud.php');
        osm_nodes_tag_cloud($json);
    }

}
    

?>