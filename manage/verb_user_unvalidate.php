<?php
    require_once('../config.php');
    require_once('auth.php');
    
    $mysqli->query('UPDATE users SET validated = 0 WHERE id = ' . $_GET['user_id']);
    
    if($mysqli->error){
        echo $mysqli->error;
    }else{
        header('Location: people.php#user-' . $_GET['user_id']);
    }
    
?>