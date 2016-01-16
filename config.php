<?php
  date_default_timezone_set('UTC');
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  
  // sessions are initiated on access tokens
  // so we want them to expire when the browser
  // is closed - kind of like basic auth.
  session_set_cookie_params(0);
  session_start();

    $db_host = 'localhost';
    $db_database = 'tenbreaths';
    $db_user = 'tenbreaths';
    $db_password = 'breaths10';
  
    // create and initialise the database connection
    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_database);    

    // connect to the database
    if ($mysqli->connect_error) {
      $returnObject['error'] = $mysqli->connect_error;
      sendResults($returnObject);
    }

    if (!$mysqli->set_charset("utf8")) {
      printf("Error loading character set utf8: %s\n", $mysqli->error);
    }
    
    
    /*
     We need to get a display string that is the local time
    */
    function getDateStringFromSurvey($survey){
      
      $local_time = $survey->started;
      
      if(isset($survey->timezoneOffset)){
        $diff_milli = abs($survey->timezoneOffset * 60 * 1000 );
        if($survey->timezoneOffset < 0){
          $local_time = $local_time +  $diff_milli;
        }else{
          $local_time = $local_time - $diff_milli;
        }
      }

      return date("D j M Y @ g:ia", $local_time/1000);
      
    }
       

  function return_json($data){
    header('Content-type: application/json');
    echo json_encode($data);
    exit(0);
  }

?>