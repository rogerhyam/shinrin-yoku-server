<?php
    require_once('../config.php');
    require_once('auth.php');
    
    $mysqli->query('DELETE FROM users WHERE id = '. $_GET['user_id']);
    
    if($mysqli->error){
        echo $mysqli->error;
    }else{
        header('Location: people.php');
    }
    
?>