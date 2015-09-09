<?php

  require_once('config.php');

   // quick and dirty KML rendering of table
   
  
   // Creates the Document.
   $dom = new DOMDocument('1.0', 'UTF-8');

   // Creates the root KML element and appends it to the root document.
   $node = $dom->createElementNS('http://earth.google.com/kml/2.1', 'kml');
   $parNode = $dom->appendChild($node);

   // Creates a KML Document element and append it to the KML element.
   $dnode = $dom->createElement('Document');
   $docNode = $parNode->appendChild($dnode);
  
  $sql = "SELECT s.*, u.display_name as user_name, u.id as user_id FROM submissions as s
JOIN api_keys as api on s.api_key_id = api.id
JOIN users as u on api.user_id = u.id
WHERE ft_row_id IS NULL AND photo IS NOT NULL";
  
  $response = $mysqli->query($sql);
  
  while($row = $response->fetch_assoc()){
      
      $submission_id = $row['id'];
      $survey_id = $row['survey_id'];
      $survey = json_decode($row['survey_json']);
      $surveyor = json_decode($row['surveyor_json']);

      // got to here -- add in photo and comment text...

      // do nothing for non georeferenced
      if(!isset($survey->geolocation->longitude) || !isset($survey->geolocation->latitude) ) continue;

      // Creates a Placemark and append it to the Document.
      $node = $dom->createElement('Placemark');
      $placeNode = $docNode->appendChild($node);

      // Creates an id attribute and assign it the value of id column.
      $placeNode->setAttribute('id', 'placemark' . $survey_id);

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
      $nameNode = $dom->createElement('name',htmlentities( $row['user_name'] . " on $date_string" ));
      $placeNode->appendChild($nameNode);
      
      /* make a description */
      $descNode = $dom->createElement('description');
      $placeNode->appendChild($descNode);
      
      $desc = "";
      
      if($row['photo']){
        
        // where are we
        if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
          $protocol = 'https://';
        }else{
          $protocol = 'http://';
        }
        
        $base_url = $protocol . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . 'data/';
        $image_url = $base_url . $row['photo'];

        $desc .= "<img src=\"$image_url\" height=\"200px\" />";

      }
      
      // comments if they made any
      if(isset($survey->textComments)){
        $comment = htmlentities($survey->textComments);
        if(strlen($comment) > 0){
          $desc .= "<p>$comment</p>";
        }
      }
      
      $descCdata = $dom->createCDATASection($desc);
      $descNode->appendChild($descCdata);

      /*
      $styleUrl = $dom->createElement('styleUrl', '#' . $row['type'] . 'Style');
      $placeNode->appendChild($styleUrl);
      */
      
      // Creates a Point element.
      $pointNode = $dom->createElement('Point');
      $placeNode->appendChild($pointNode);

      // Creates a coordinates element and gives it the value of the lng and lat columns from the results.
      $coorStr = $survey->geolocation->longitude . ','  . $survey->geolocation->latitude;
      $coorNode = $dom->createElement('coordinates', $coorStr);
      $pointNode->appendChild($coorNode);

  }
  
  $kmlOutput = $dom->saveXML();
  //header('Content-type: application/vnd.google-earth.kml+xml');
  header('Content-type: text/xml');
  echo $kmlOutput;
  
  

?>