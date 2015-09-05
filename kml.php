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



   
  $response = $mysqli->query('SELECT * FROM submissions WHERE ft_row_id IS NULL'); // FIXME - add check for moderated
  while($row = $response->fetch_assoc()){
      
      $submission_id = $row['id'];
      $survey_id = $row['survey_id'];
      $survey = json_decode($row['survey_json']);
      $surveyor = json_decode($row['surveyor_json']);

      // do nothing for non georeferenced
      if(!isset($survey->geolocation->longitude) || !isset($survey->geolocation->latitude) ) continue;

        // Creates a Placemark and append it to the Document.
        $node = $dom->createElement('Placemark');
        $placeNode = $docNode->appendChild($node);

        // Creates an id attribute and assign it the value of id column.
        $placeNode->setAttribute('id', 'placemark' . $survey_id);

        $nameNode = $dom->createElement('name',htmlentities( $survey_id ));
        $placeNode->appendChild($nameNode);
        $descNode = $dom->createElement('description', 'Some description text');
        $placeNode->appendChild($descNode);

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