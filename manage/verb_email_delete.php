<?php
    require_once('../config.php');
    require_once('auth.php');
    
    $mysqli->query('DELETE FROM email_queue WHERE id = '. $_GET['email_id']);
    
    if($mysqli->error){
        echo $mysqli->error;
    }else{
        header('Location: email.php');
    }
    
?>