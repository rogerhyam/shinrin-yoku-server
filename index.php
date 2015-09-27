<!DOCTYPE html>
<?php
  
  require_once('config.php');
  header("Access-Control-Allow-Origin: *");
  
  
  // where are we
  if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
    $protocol = 'https://';
  }else{
    $protocol = 'http://';
  }
  $site_url = $protocol . $_SERVER['SERVER_NAME'] . '/';
  
  // default values for center and zoom
  $center_lon = -3.210008;
  $center_lat = 55.964747;
  $zoom = false;
  $preserve_viewport = 'false';
  
  // have we been passed a survey id?
  // if so override the zoom and center
  $survey_id = false;
  $fb_tags = array();
  if(isset($_GET['survey']) && strlen($_GET['survey']) < 40 && strpos($_GET['survey'], ' ') === false ){
    // e.g. 2710f20e-6511-4110-9030-d67033c632a0
    
    $survey_id = $_GET['survey'];
    $sql = "SELECT s.survey_json, s.photo, u.display_name 
      FROM submissions as s JOIN api_keys as a ON s.api_key_id = a.id
      JOIN users as u ON a.user_id = u.id
      WHERE s.survey_id = '$survey_id'";
    $response = $mysqli->query($sql);
    
    if($response->num_rows){
      $row = $response->fetch_assoc();
      $survey = json_decode($row['survey_json']);
      
      // set up the location so the popup will be displayed
      if(isset($survey->geolocation->longitude) && isset($survey->geolocation->latitude)){
        $center_lon = $survey->geolocation->longitude;
        $center_lat = $survey->geolocation->latitude;
        $preserve_viewport = 'true';
      }
      
      // set up the facebook meta tagging so fb can crawl the page
      $fb_tags['og:url']  = $site_url . 'survey-' . $survey_id;
      $fb_tags['og:type']  = 'place';
      $fb_tags['og:site_name'] = 'Ten Breaths Map';
      $fb_tags['place:location:latitude'] = $survey->geolocation->latitude;
      $fb_tags['place:location:longitude'] = $survey->geolocation->longitude;
      $fb_tags['og:title'] = 'A Ten Breaths Place by ' . $row['display_name'] . ' on ' . getDateStringFromSurvey($survey);
      if(isset($survey->textComments)){
        $comment = $survey->textComments;
        if(strlen($comment) > 0){
         $fb_tags['og:description'] = $survey->textComments;
        }
      }
      if($row['photo']){
         $fb_tags['og:image'] = $site_url . 'data/' . $row['photo'];
      }
      
    } // end found row
    
  }// end have survey_id
  
  // have we been passed a center point and zoom level - possibly for "nearby" functionality
  if(isset($_GET['center'])){
    $center = $_GET['center'];
  }
  if(isset($_GET['zoom'])){
    $zoom = $_GET['zoom'];
  }

?>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml" >
  <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# place: http://ogp.me/ns/place#">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    
    <!-- Facebook Meta -->
    <?php foreach($fb_tags as $property => $content){ ?>
      <meta property="<?php echo $property ?>" content="<?php echo $content; ?>" />
    <?php } // end for each tag ?>
  
    <title>Ten Breaths Map</title>
    <link rel="stylesheet" href="js/openlayers-3.9.0/ol.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="style/main.css">
    
    <script src="js/jquery-1.11.3.min.js" type="text/javascript"></script>
    <script src="js/openlayers-3.9.0/ol.js" type="text/javascript"></script>
    <script src="js/main.js" type="text/javascript"></script>
    
  </head>
  <body>
    <header>
      <button>Menu</button>
      <h1>The Ten Breaths Map</h1>
      
    </header>
    <div id="appcontent">
        <div
          id="map-canvas"
          class="map-canvas" 
          data-tb-center-lon="<?php echo $center_lon; ?>"
          data-tb-center-lat="<?php echo $center_lat; ?>"
          <?php if($zoom){ ?>
              data-tb-zoom="<?php echo $zoom; ?>"
          <?php }else{ ?>
              data-tb-zoom="<?php echo 10; ?>"
          <?php } ?>
          data-tb-survey="<?php echo $survey_id; ?>"
          ></div>
    </div>
    <div id="popup" class="ol-popup">
      <a href="#" id="popup-closer" class="ol-popup-closer"></a>
      <div id="popup-content"></div>
      <!-- fb share button code -->
      <button id="popup-fb-share" data-tenbreaths-base-url="<?php echo $site_url ?>">Share on FaceBook</button>
    </div>
  </body>
</html>

