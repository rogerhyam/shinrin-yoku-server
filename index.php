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

?>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Ten Breaths Map</title>
    
    <link rel="stylesheet" type="text/css" href="style/main.css">
    
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>
    <script>
        function initialize() {
         
          var edinburgh = new google.maps.LatLng(55.964747,-3.210008);
          var mapOptions = {
            zoom: 11,
            center: edinburgh
          }

          var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

          var ctaLayer = new google.maps.KmlLayer({
           url: '<?php echo $kml_url ?>'
          });
          ctaLayer.setMap(map);
        }
        
        google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
    
    <header>
      <button>Menu</button>
      <h1>The Ten Breaths Map</h1>
      
    </header>
    <div id="appcontent">
        <div id="map-canvas"></div>
    </div>

  </body>
</html>

