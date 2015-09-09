<?php
  date_default_timezone_set('UTC');
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

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
?>