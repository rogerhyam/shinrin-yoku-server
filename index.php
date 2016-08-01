<!DOCTYPE html>
<?php
  require_once('config.php');
  
  header("Access-Control-Allow-Origin: *");
  
  // log us in if we are passing an access token
  if(isset($_GET['t'])){
    require_once('submit/authentication.php');
    authentication_by_token($_GET['t']);
  }
  
  // default values for center and zoom
  $center_lon = 0;
  $center_lat = 0;
  $zoom = 0;
 
  // have we been passed a survey id?
  // if so override the zoom and center
  $survey_key = false;
  $fb_tags = array();
  $twitter_tags = array();
  if(isset($_GET['survey']) && strlen($_GET['survey']) < 40 && strpos($_GET['survey'], ' ') === false ){
    // e.g. 2710f20e-6511-4110-9030-d67033c632a0
    
    $survey_key = $_GET['survey'];
    $sql = "
      SELECT s.survey_json, s.photo, u.display_name 
      FROM submissions as s
      JOIN users as u ON s.user_id = u.id
      WHERE s.survey_key = '$survey_key'
      ";
    
    $response = $mysqli->query($sql);
    error_log($mysqli->error);
    
    if($response->num_rows){
      
      $row = $response->fetch_assoc();
      $survey = json_decode($row['survey_json']);
      
      // set up the location so the popup will be displayed
      if(isset($survey->geolocation->longitude) && isset($survey->geolocation->latitude)){
        $center_lon = $survey->geolocation->longitude;
        $center_lat = $survey->geolocation->latitude;
        $zoom = 15;
      }
      
      // social media here we come
      $social_title = 'A Ten Breaths Place by ' . $row['display_name'] . ' on ' . getDateStringFromSurvey($survey);
      
      // set up the facebook meta tagging so fb can crawl the page
      $fb_tags['og:url']  = get_server_uri() . 'survey-' . $survey_key;
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
         $image_url = get_server_uri() . 'data/' . $row['photo'];
         $fb_tags['og:image'] = $image_url;
         $twitter_tags['twitter:image'] = $image_url;
      }
      
      // set up the twitter card specific meta tagging
      $twitter_tags['twitter:card'] = 'summary_large_image';
      $twitter_tags['twitter:creator'] = '@rogerhyam';
      $twitter_tags['twitter:site'] = '@tenbreathsmap';
  
    } // end found row
    
  }// end have survey_key
  
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
    <script src="js/openlayers-3.12.1/ol.js" type="text/javascript"></script>
    <script src="js/main.js" type="text/javascript"></script>
    
    
  </head>
  <body>
    <header>
      <div id='cssmenu'>
          <ul>
             <li><a href="/">Ten Breaths</a></li>
             <li><a id="menu-nearby-link" href='#' onclick="mapsConfig.zoomToCurrentLocation()" >Nearby</a></li>
             <li><a href='#' onclick="mapsConfig.showPopupPage('about-page')" >About</a></li>
             <?php if(@$_SESSION['user_key']){ ?>
                 <li><a href="/?t=logout" >Log Out</a></li>
             <?php } // user key check ?>
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
          
          data-tb-survey="<?php echo $survey_key; ?>"
          data-tb-user-key="<?php echo isset($_SESSION['user_key']) ? $_SESSION['user_key'] : ""; ?>"
          
          data-tb-pop-page="<?php echo @$_GET['pp'] ?>"
          
          ></div>
    </div>
    <div id="popup" class="ol-popup" data-tenbreaths-base-url="<?php echo get_server_uri() ?>">
      <a href="#" id="popup-closer" class="ol-popup-closer"></a>
      <div id="popup-content"></div>
      <div id="popup-buttons">
        <button id="popup-fb-share" >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" id="gel-icon-facebook">
            <path d="M12.1 32V17H8v-5.4h4.1V7c0-3.6 2.4-7 8-7 2.2 0 3.9.2 3.9.2l-.1 5.1h-3.6c-2 0-2.3.9-2.3 2.4v3.9h6l-.3 5.4H18v15h-5.9z"></path>
          </svg>
          <br/>Facebook
        </button>
        <button id="popup-twitter-share" >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" id="gel-icon-twitter">
            <path d="M32 6.1c-1.2.5-2.4.9-3.8 1 1.4-.8 2.4-2.1 2.9-3.6-1.3.8-2.7 1.3-4.2 1.6C25.7 3.8 24 3 22.2 3c-3.6 0-6.6 2.9-6.6 6.6 0 .5.1 1 .2 1.5-5.5-.3-10.3-2.9-13.6-6.9-.6 1-.9 2.1-.9 3.3 0 2.3 1.2 4.3 2.9 5.5-1.1 0-2.1-.3-3-.8v.1c0 3.2 2.3 5.8 5.3 6.4-.6.1-1.1.2-1.7.2-.4 0-.8 0-1.2-.1.8 2.6 3.3 4.5 6.1 4.6-2.2 1.8-5.1 2.8-8.2 2.8-.5 0-1.1 0-1.6-.1 3 1.8 6.5 2.9 10.2 2.9 12.1 0 18.7-10 18.7-18.7v-.9c1.2-.9 2.3-2 3.2-3.3z"/>
          </svg>
          <br/>Twitter
        </button>
        <button id="popup-mail" >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" id="gel-icon-email">
            <path d="M16 19.4l16-15V3H0v26h32V8l-4 4v13H4V8.2l12 11.2zm0-2.8L5.8 7h20.4L16 16.6z"/>
          </svg>
          <br/>Email
        </button>
        <button id="popup-google-maps" >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" id="gel-icon-map">
            <path d="M25.1 17.2c1.2-1.8 1.9-3.9 1.9-6.2 0-6.1-4.9-11-11-11S5 4.9 5 11c0 2.3.7 4.4 1.9 6.2L16 32l9.1-14.8zM16 7c2.2 0 4 1.8 4 4s-1.8 4-4 4-4-1.8-4-4 1.8-4 4-4z"/>
          </svg>
          <br/>Google
        </button>
        <button id="popup-report" >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" id="gel-icon-alert">
            <path d="M16 2L0 30h32L16 2zm2 25h-4v-4h4v4zm-4-6V11h4v10h-4z"/>
          </svg>
          <br/>Report
        </button>
      </div>
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
         Points are added to the map by contributors doing a simple mindfulness practice at the locations they find restorative when they find them restorative.</p>
      <p>The mindfulness practice is an established Buddhist technique. It involves holding ones attention still whilst taking ten conscious breaths.</p>
      <p>The mobile phone app records the precise location of the place whilst helping the contributor count their breaths.
      It also enables them to add a photo and some words that sum up their experience to the report.</p>
      
      <p>The act of surveying locations for the map should be an act of healing.
      In drawing the map we aim to deepen the contributots' connections with nature
      as well as produce data that is useful for science.</p>
      
      <h3>Video Introduction</h3>
      <p id="video-frame-wrapper"><iframe width="560" height="315" src="https://www.youtube.com/embed/3_1LlnHLwiw" frameborder="0" allowfullscreen></iframe></p>
      
      <h3>Get the App</h3>
      
      <p>
        <a href='https://play.google.com/store/apps/details?id=uk.org.rbge.hyam.tenbreathsmap&utm_source=global_co&utm_medium=prtnr&utm_content=Mar2515&utm_campaign=PartBadge&pcampaignid=MKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1'><img id="google-badge" alt='Get it on Google Play' src='https://play.google.com/intl/en_us/badges/images/generic/en_badge_web_generic.png'/></a>
          <a href="https://itunes.apple.com/gb/app/ten-breaths-map/id1109365108?mt=8"><img id="apple-badge" src="images/apple_badge_135x40.png" /></a>
      </p>
      
      <h3>Status</h3>
      <p>
        <ul>
          <li>
            <strong>1<sup>st</sup> August 2016: </strong> Soft launch day. After a few teething problems and now with a new introduction video we are starting a gentle roll out to friendly testers.
            If you are interested please download the app and let us know how you get on.
          </li>
          <li><strong>1<sup>st</sup> May 2016: </strong> We have just gone live.</li>
          <li>Version 1.0 of the app is available in the Apple App Store and in the Google Play Store.</li>
          <li>We are looking for our first batch adventurous contributors.</li>
          <li>If you would like to participate please download the app. </li>
        </ul>
      </p>
      
      <h3>About Us</h3>
     		<p>
				    <a href="http://www.rbge.org.uk"><img id="rbge-logo" src="images/rbge_logo.jpg"/></a>
				</p>
				<p>
					The Ten Breaths Map is being coordinated by the 
					<a href="http://www.rbge.org.uk">Royal Botanic Garden Edinburgh</a>
					as part of our contribution to the Edinburgh Living Landscape and
					<a href="http://www.gov.scot/Topics/Environment/Wildlife-Habitats/biodiversity/BiodiversityStrategy">
						Scotland’s Biodiversity Strategy</a>
					 - particularly
					<strong>Big Step 3:</strong> <em>Quality Greenspace for Health and Education Benefits</em>
					- by providing the evidence to deliver
					<strong>Priority Project 5:</strong> <em>More People Experiencing and Enjoying Nature</em>
					and
					<strong>Priority Project 7:</strong> <em>Developing Scotland’s Natural Health Service</em>.
				</p>
				<p>
				    <img id="ell-logo" src="images/ell_logo.jpg"/>
				</p>
				<p>
					Although these first projects concern Edinburgh and Scotland the map
					covers the entire globe and could be used for projects, campaigns or
					fun anywhere. The software is open source and we intend to keep the data
					as open as we can without compromising contributors' privacy. If you have project
					ideas please get in touch and we will do what we can to support and partner with you.
				</p>
				
		  <h3>See Also</h3>
      <p>The book <a href="http://www.amazon.co.uk/dp/1937006395">Ten Breaths To Happiness: Touching Life in Its Fullness </a> by Glen Schneider
      is a great description of the mindfulness technique to use and background reading - highly recommended.</p>
      
      <a href="#" class="popup-page-closer">close</a>
      
    </div>
    
    <!-- message page -->
    <div id="message-page" class="popup-page">
      <a href="#" class="popup-page-closer">close</a>
      <h2><?php echo @$_SESSION['message_title'] ?></h2>
      <?php echo @$_SESSION['message_body'] ?>
    </div>
    
  </body>
</html>

