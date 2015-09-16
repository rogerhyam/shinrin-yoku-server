<!DOCTYPE html>
<?php
  
  require_once('config.php');
  
  // where are we
  if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
    $protocol = 'https://';
  }else{
    $protocol = 'http://';
  }
  
  // get the last inserted submission as our revion number
  $response = $mysqli->query('SELECT MAX(id) as rev FROM submissions');
  if($response->num_rows){
    $row = $response->fetch_assoc();
    $rev = $row['rev'];
  }else{
    $rev = 'none';
  }
  
  $kml_url = $protocol . $_SERVER['SERVER_NAME'] . '/kml.php?rev=' . $rev;
  
  // default values for center and zoom
  $center_lon = -3.210008;
  $center_lat = 55.964747;
  $zoom = false;
  $preserve_viewport = 'false';
  
  // have we been passed a survey id?
  // if so override the zoom and center
  $survey_id = false;
  if(isset($_GET['survey']) && strlen($_GET['survey']) < 40 && strpos($_GET['survey'], ' ') === false ){
    // e.g. 2710f20e-6511-4110-9030-d67033c632a0
    
    $survey_id = $_GET['survey'];
    $sql = "SELECT survey_json FROM submissions WHERE survey_id = '$survey_id'";
    $response = $mysqli->query($sql);
    
    if($response->num_rows){
      $row = $response->fetch_assoc();
      $survey = json_decode($row['survey_json']);
      if(isset($survey->geolocation->longitude) && isset($survey->geolocation->latitude)){
        $center_lon = $survey->geolocation->longitude;
        $center_lat = $survey->geolocation->latitude;
        $zoom = 17;
        $preserve_viewport = 'true';
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
  
  // have we been asked to restrict to a username?
  if(isset($_GET['username'])){
    $kml_url .= '&username=' . $_GET['username'];
  }
  

?>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
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
          <?php } ?>
          data-tb-survey="<?php echo $survey_id; ?>"
          ></div>
    </div>
    <div id="popup" class="ol-popup">
      <a href="#" id="popup-closer" class="ol-popup-closer"></a>
      <div id="popup-content"></div>
    </div>
  </body>
</html>

