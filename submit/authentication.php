<?php

require_once('../config.php');

function authentication_signup(){
    
    global $mysqli;
    $out = array();
    $errors = array();
    
    // what are we interested in
    $display_name = @$_POST['display_name'];
    $email = trim(@$_POST['email']);
    $password = @$_POST['password'];
    
    // been passed reasonable values
    // check email -- looks OK
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "The email is invalid";
    }
    
    // is email already registered
    $stmt = $mysqli->prepare("SELECT id FROM `users` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    error_log($mysqli->error);
    $stmt->store_result();
    if($stmt->num_rows > 0){
        $errors[] = "This email address is already registered. Have you forgotten your password?";
    }
    
    // does the username look OK?
    if(!$display_name || strlen($display_name) < 6){
        $errors[] = "The display Name must be more than 5 characters long.";
    }
    
    // is the username taken
    $stmt = $mysqli->prepare("SELECT id FROM `users` WHERE display_name = ?");
    $stmt->bind_param("s", $display_name);
    $stmt->execute();
    error_log($mysqli->error);
    $stmt->store_result();
    if($stmt->num_rows > 0){
        $errors[] = "The display name '$display_name' is taken. Please choose another.";
    }
    
    // does the password look OK?
    if(!$password || strlen($password) < 7){
        $errors[] = "The password must be more than 6 characters long '$password' .";
    }
    
    // if no errors at this stage try and create users
    if(count($errors) == 0){
        // create the user row
        $user_key = uniqid('USER:', true);
        $password_hash = md5($password);
        $stmt = $mysqli->prepare("INSERT INTO users (`display_name`, `email`, `password`, `key`, `created`) VALUES (?, ?, ?, ?, now())");
        error_log($mysqli->error);
        $stmt->bind_param("ssss", $display_name, $email, $password_hash, $user_key);
        $stmt->execute();
        if($stmt->affected_rows != 1){
            error_log($mysqli->error);
            $errors[] = "Database error. Please try later.";
            $stmt->close();
        }else{
            $id = $stmt->insert_id;
            error_log("CREATED user: " . $id);
            $out['userKey'] = $user_key;
            $out['displayName'] = $display_name;
            $stmt->close();
        }
    }
    
    // return some json 
    if(count($errors) > 0){
        $out['success'] = false;
        $out['errors'] = $errors;
    }else{
        $out['success'] = true;
    }
    
    return_json($out);
    
    
}

function authentication_login(){
    
    global $mysqli;
    $out = array();
    $errors = array();
    
    // what are we interested in
    $email = @$_POST['email'];
    error_log($email);
    $password = @$_POST['password'];
    $password_hash = md5($password);
    error_log($password_hash);
    
    // look in the db
    $stmt = $mysqli->prepare("SELECT `display_name`, `key` FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password_hash);
    $stmt->execute();
    $stmt->store_result();
    
    if($stmt->num_rows == 1){
        $stmt->bind_result($display_name, $user_key);
        $stmt->fetch();
        $out['success'] = true;
        $out['displayName'] = $display_name;
        $out['userKey'] = $user_key;
    }else{
        $out['success'] = false;
    }
    
    return_json($out);
    
}

?>