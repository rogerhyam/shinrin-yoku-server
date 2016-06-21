<?php
    require_once('../config.php');
    require_once('auth.php');
    
    $mysqli->query('UPDATE submissions SET public = 1 WHERE id = ' . $_GET['id']);
    
    if($mysqli->error){
        echo $mysqli->error;
    }else{
        header('Location: index.php#submission-' . $_GET['id']);
    }
    
?>