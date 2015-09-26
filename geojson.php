<?php
  require_once('config.php');

    $sql = "SELECT s.*, u.display_name as username, u.id as user_id FROM submissions as s
JOIN api_keys as api on s.api_key_id = api.id
JOIN users as u on api.user_id = u.id
WHERE ft_row_id IS NULL";

  // basic filter is to restrict to usernam
  if(isset($_GET['username']) && strlen($_GET['username']) < 40){
    $sql .= " AND u.display_name = '" . $_GET['username'] ."'";
  }

  $response = $mysqli->query($sql);
  $features = array();
  while($row = $response->fetch_assoc()){
      
      $submission_id = $row['id'];
      $survey_id = $row['survey_id'];
      $survey = json_decode($row['survey_json']);
      $surveyor = json_decode($row['surveyor_json']);
  
      if(!isset($survey->geolocation->longitude) || !isset($survey->geolocation->latitude) ) continue;
      
      // create a point feature
      $feature = new stdClass();
      $features[] = $feature;
      $feature->id = $survey_id;
      $feature->type = "Feature";
      $feature->geometry = new stdClass();
      $feature->geometry->type = "Point";
      $feature->geometry->coordinates = [$survey->geolocation->longitude, $survey->geolocation->latitude ];
      
      // add some properties to it
      $feature->properties = new stdClass();
      
      // a name for the place - based on the username and data
      $local_time = $survey->started;
      
      if(isset($survey->timezoneOffset)){
        $diff_milli = abs($survey->timezoneOffset * 60 * 1000 );
        if($survey->timezoneOffset < 0){
          $local_time = $local_time +  $diff_milli;
        }else{
          $local_time = $local_time - $diff_milli;
        }
      }
      $date_string = date("D j M Y @ g:ia", $local_time/1000);
      $feature->properties->title = $row['username'] . " on $date_string";
      
      $desc = '<div>';
      if($row['photo']){
        
        // where are we
        if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
          $protocol = 'https://';
        }else{
          $protocol = 'http://';
        }
        
        $base_url = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . 'data/';
        $image_url = $base_url . $row['photo'];

        $desc .= "<img src=\"$image_url\"  />";

      }
      
      // comments if they made any
      if(isset($survey->textComments)){
        $comment = $survey->textComments;
        if(strlen($comment) > 0){
          $desc .= "<p>$comment</p>";
        }
      }

      
      $feature->properties->description = $desc;

  }
  
  $geojson = new stdClass();
  $geojson->type = 'FeatureCollection';
  $geojson->features = $features;
  
  header('Content-Type: application/vnd.geo+json');
  echo json_encode($geojson);
?>
  
