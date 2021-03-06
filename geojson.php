<?php
  require_once('config.php');
  
  // look to the session then access token for the user_key.
  if(isset($_SESSION['user_key'])){
    $user_key = $_SESSION['user_key'];
  }elseif(isset($_GET['t'])){
    require_once('submit/authentication.php');
    $user_key = authentication_by_token($_GET['t']);
  }else{
    $user_key = null;
  }
  
  // If it isn't there leave out all non-public records
  // If it is there then include the hidden ones for that user.

    $sql = "
      SELECT
          s.*,
          u.display_name as username,
          u.id as user_id,
          u.key as user_key
      FROM 
          submissions as s
      JOIN
          users as u on s.user_id = u.id
      WHERE
        ( s.`public` = 1 AND  u.validated = 1 )
      ";
  
  // they are logged in so also show their hidden one
  if($user_key){
    $sql .= " OR ( u.key = '$user_key' )";
  }

  $response = $mysqli->query($sql);
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
      
      $feature->properties->title = $row['username'] . " on " . getDateStringFromSurvey($survey);
      $feature->properties->user_key = $row['user_key'];
      $feature->properties->public_visible = $row['public'];
      
      $desc = '';
      if($row['photo']){
        
        // where are we
        if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
          $protocol = 'https://';
        }else{
          $protocol = 'http://';
        }
        
        $base_url = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . 'data/';
        $image_url = $base_url . $row['photo'];

        $desc .= "<img src=\"$image_url\" />";
        
        $feature->properties->has_image = true;

      }else{
        $feature->properties->has_image = false;
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
  
