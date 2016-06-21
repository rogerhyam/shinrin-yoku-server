<?php
  date_default_timezone_set('UTC');
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // load the composer stuff  
  require 'vendor/autoload.php';

  
  // sessions are initiated on access tokens
  // so we want them to expire when the browser
  // is closed - kind of like basic auth.
  session_set_cookie_params(0);
  session_start();
  
  // include secret db details - no matter depth
  $path = str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/'));
  require_once($path . 'tenbreaths_db_config.php');
  
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
  
  /*
   * Adds an email to the email_queue table
   * a cron script will then send it
   *
   */
  function enqueue_email($kind, $to_address, $to_name, $subject, $body){
    
    global $mysqli;
    
    $stmt = $mysqli->prepare('INSERT INTO email_queue (kind, to_address, to_name, subject, body, created) VALUES (?,?,?,?,?, now())');
    echo $mysqli->error;
    $stmt->bind_param("sssss", $kind, $to_address, $to_name, $subject, $body );
    
    $stmt->execute();
    if($mysqli->error){
      error_log($mysqli->error);
      return false;
    }else{
      return true;
    }
    
    
    
  }
  
  // we need to know where we are
  function get_server_uri(){
      if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ){
        $protocol = 'https://';
      }else{
        $protocol = 'http://';
      }
      return $protocol . $_SERVER['SERVER_NAME'] . '/';
  }


?>