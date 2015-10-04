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
  $twitter_tags = array();
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
      
      // social media here we come
      $social_title = 'A Ten Breaths Place by ' . $row['display_name'] . ' on ' . getDateStringFromSurvey($survey);

      
      // set up the facebook meta tagging so fb can crawl the page
      $fb_tags['og:url']  = $site_url . 'survey-' . $survey_id;
      $fb_tags['og:type']  = 'place';
      $fb_tags['og:site_name'] = 'Ten Breaths Map';
      $fb_tags['place:location:latitude'] = $survey->geolocation->latitude;
      $fb_tags['place:location:longitude'] = $survey->geolocation->longitude;
      $social_title = 'A Ten Breaths Place by ' . $row['display_name'] . ' on ' . getDateStringFromSurvey($survey);
      $fb_tags['og:title'] = $social_title;
      $twitter_tags['twitter:title'] = $social_title;
      if(isset($survey->textComments)){
        $comment = $survey->textComments;
        if(strlen($comment) > 0){
         $fb_tags['og:description'] = $survey->textComments;
         $twitter_tags['twitter:description'] = $survey->textComments;
        }
      }
      if($row['photo']){
         $image_url = $site_url . 'data/' . $row['photo'];
         $fb_tags['og:image'] = $image_url;
         $twitter_tags['twitter:image'] = $image_url;
      }
      
      // set up the twitter card specific meta tagging
      $twitter_tags['twitter:card'] = 'summary_large_image';
      $twitter_tags['twitter:creator'] = '@rogerhyam';
      $twitter_tags['twitter:site'] = '@tenbreathsmap';
  
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
    
    <!-- Twitter Meta -->
    <?php foreach($twitter_tags as $name => $content){ ?>
      <meta name="<?php echo $name ?>" content="<?php echo $content; ?>" />
    <?php } // end for each tag ?>
  
    <title>Ten Breaths Map</title>
    <link rel="stylesheet" href="js/openlayers-3.9.0/ol.css" type="text/css">
    <link rel="stylesheet" type="text/css" href="style/main.css">
    <link rel="stylesheet" type="text/css" href="style/cssmenu/styles.css">
    
    <script src="js/jquery-1.11.3.min.js" type="text/javascript"></script>
    <script src="style/cssmenu/script.js" type="text/javascript"></script>
    <script src="js/openlayers-3.9.0/ol.js" type="text/javascript"></script>
    <script src="js/mapsconfig.js" type="text/javascript"></script>
    <script src="js/main.js" type="text/javascript"></script>
    
    
  </head>
  <body>
    <header>
      <div id='cssmenu'>
          <ul>
             <li><a href="/">Ten Breaths</a></li>
             <li class='active'><a href='#'>Maps</a>
                <ul id="maps-sub-menu">
                   <li><a href='#'>Edinburgh</a></li>
                   <li><a href='#'>Scotland</a></li>
                </ul>
             </li>
             <li><a href='#'>Layers</a>
                <ul id="layers-sub-menu" >
                   <li><a href='#'>Base Layer</a>
                     <ul id="base-layers-sub-menu" >
                     </ul>
                   </li>
                   <li><a href='#'>Edinburgh</a></li>
                   <li><a href='#'>Scotland</a></li>
                </ul>
                </li>
             <li><a href='#' onclick="mapsConfig.showPopupPage('about-page')" >About</a></li>
             <li><a href='#' onclick="mapsConfig.showPopupPage('contact-page')" >Contact</a></li>
          </ul>
      </div>
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
    <div id="popup" class="ol-popup" data-tenbreaths-base-url="<?php echo $site_url ?>">
      <a href="#" id="popup-closer" class="ol-popup-closer"></a>
      <div id="popup-content"></div>
      <button id="popup-fb-share" >FaceBook</button>
      <button id="popup-twitter-share" >Twitter</button>
      <button id="popup-link" >Link</button>
      <button id="popup-info" >Info</button>
    </div>
    
    <!-- About page -->
    <div id="about-page" class="popup-page">
      <a href="#" class="popup-page-closer">close</a>
      <h2>About Ten Breaths Map</h2>
      <p><strong>The Ten Breaths Map is a map of the natural places that nourish and heal 
      made by the very people who are nourished and healed by those places.</strong>
      Its aim is to link together individuals' felt experiences of places with data about the natural world that supports those experiences. 
      It is hoped the map will inform decision making in urban planning, health and nature conservation.
      </p>
      <p>The system consists of a mobile phone app and database exposed through this website.
         Points are added to the map by surveyors doing a simple mindfulness practice at the locations they find restorative when they find them restorative.</p>
      <p>The mindfulness practice is an established Buddhist technique. It involves holding ones attention still whilst taking ten conscious breaths.</p>
      <p>The mobile phone app records the precise location of the place whilst helping the surveyor count their breaths.
      It also enables them to add a photo and some words that sum up their experience to the report.</p>
      
      <p>The act of surveying locations for the map should be an act of healing.
      In drawing the map we aim to deepen the surveyors' connections with nature
      as well as produce data that is useful for science.</p>
      
      <h3>Status</h3>
      <p>We are still in the "playing" phase where we test out different ideas and technologies to see what is feasible.
      This is way before a Beta phase or even and Alpha phase!</p>
      
      <h3>Thanks</h3>
      <p>The data data layers presented on the map that start with SNH are pulled live from the 
      <a href="http://www.snh.gov.uk/publications-data-and-research/snhi-information-service/">Scottish Natural Heritage Information Service</a> under an 
      <a href="http://www.nationalarchives.gov.uk/doc/open-government-licence/" >Open Government Licence</a>.</p>
      <p>The book <a href="http://www.amazon.co.uk/dp/1937006395">Ten Breaths To Happiness: Touching Life in Its Fullness </a> by Glen Schneider was 
      helped to clarify the mindfulness technique to use and provides a good background reading.</p>
      
    </div>
    
    <!-- Contact page -->
    <div id="contact-page" class="popup-page">
      <a href="#" class="popup-page-closer">close</a>
      <h2>Contact Us</h2>
      <p>Any questions or comments? I'd love to hear from you. Roger Hyam <a href="mailto:r.hyam@rbge.org.uk">r.hyam@rbge.org.uk</a></p>
    </div>
    
    <!-- Contact page -->
    <div id="info-page" class="popup-page">
      <a href="#" class="popup-page-closer">close</a>
      <h2>Survey Information</h2>
      <div class="content">
        <p>This will be replaced by details of the survey point.</p>
      </div>
    </div>
    
  </body>
</html>

