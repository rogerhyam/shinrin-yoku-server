<?php

  require_once('../config.php');

  // run through all the records that haven't been published
  $response = $mysqli->query('SELECT * FROM submissions WHERE ft_row_id IS NULL'); // FIXME - add check for moderated
  while($row = $response->fetch_assoc()){
      
      $submission_id = $row['id'];
      $survey_id = $row['survey_id'];
      $survey = json_decode($row['survey_json']);
      $surveyor = json_decode($row['surveyor_json']);
      
      print_r($survey);
      echo '<hr/>';
      
  }
  
  
  

?>