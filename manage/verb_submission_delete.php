<?php
    require_once('../config.php');
    require_once('auth.php');
    
    $mysqli->query('DELETE FROM submissions WHERE id = '. $_GET['id']);
    
    if(@$_GET['photo_path']){
        unlink($_GET['photo_path']);
    }
    
    if($mysqli->error){
        echo $mysqli->error;
    }else{
        header('Location: index.php');
    }
    
?>