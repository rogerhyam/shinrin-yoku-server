<?php
  require_once('config.php');
  
  // where are we
  if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
    $protocol = 'https://';
  }else{
    $protocol = 'http://';
  }
  
  $base_url = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
  
  // FIXME: Here we check if we are being called from the local host and 
  // also if a key is included and if so we allow download of hidden records
  $restricted = true;
  
  // If it isn't there leave out all non-public records
  // If it is there then include the hidden ones for that user.

    $sql = "
      SELECT
          s.*,
          
          u.display_name as username,
          u.id as user_id,
          u.key as user_key,
        
          osm_address.raw as osm_address_json
          
          
          
      FROM 
          submissions as s
      JOIN
          users as u on s.user_id = u.id
      JOIN
          au_osm_reverse_geocode as osm_address on osm_address.submission_id = s.id
      ";
      
  if($restricted){
    $sql .= " WHERE s.`public` = 1  ";
  }
  $response = $mysqli->query($sql);
  if($mysqli->error){
    echo $mysqli->error;
    exit();
  }
  $features = array();
  while($row = $response->fetch_assoc()){
      
      $submission_id = $row['id'];
      $survey_key = $row['survey_key'];
      $survey = json_decode($row['survey_json']);
  
      if(!isset($survey->geolocation->longitude) || !isset($survey->geolocation->latitude) ) continue;
      
      // create a point feature
      $feature = new stdClass();
      $features[] = $feature;
      $feature->id = $survey_key;
      $feature->type = "Feature";
      $feature->geometry = new stdClass();
      $feature->geometry->type = "Point";
      $feature->geometry->coordinates = [$survey->geolocation->longitude, $survey->geolocation->latitude ];
      
      // add some properties to it
      $feature->properties = new stdClass();

      // we get some properties from the original survey object
      $feature->properties->title = $row['username'] . " on " . getDateStringFromSurvey($survey);
      $feature->properties->user_key = $row['user_key'];
      $feature->properties->public_visible = $row['public'];
      
      
      if($row['osm_address_json']){
        $osm_address = json_decode($row['osm_address_json']);
        $feature->properties->osm_address_place_id = $osm_address->place_id;
        $feature->properties->osm_address_id = $osm_address->osm_id;
        $feature->properties->osm_address_display_name = $osm_address->display_name;
        if(isset($osm_address->address)){
          if(isset($osm_address->address->postcode)) $feature->properties->osm_address_postcode = $osm_address->address->postcode;
          if(isset($osm_address->address->country_code))  $feature->properties->osm_address_country_code = $osm_address->address->country_code;
        }
      }
      
      if($row['photo']){
        $feature->properties->photo = $base_url . 'data/' . $row['photo'];
      }
      
      // link to survey on site
      $feature->properties->uri = $base_url . 'survey-' . $survey_key;
      
      
      // comments if they made any
      if(isset($survey->textComments)){
        $feature->properties->comment = $survey->textComments;
      }

  }
  
  $geojson = new stdClass();
  $geojson->type = 'FeatureCollection';
  $geojson->features = $features;
  
  header('Content-Type: application/vnd.geo+json');
  echo json_encode($geojson);
?>
  

