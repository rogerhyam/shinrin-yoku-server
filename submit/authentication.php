<?php

function authentication_by_token($token){
    
    global $mysqli;
    
    // if the token matches one issued to a user then
    // add that user's key to the session to signify 
    // they are logged in to the site.
    $stmt = $mysqli->prepare('SELECT  display_name, `key` FROM users WHERE access_token = ?');
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0){
        $stmt->bind_result($_SESSION['display_name'], $_SESSION['user_key']);
        $stmt->fetch();
        return $_SESSION['user_key'];
    }else{
        return null;
    }
    
}

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
    
    // they will need an access token
    $access_token = authentication_generate_access_token();
    
    // if no errors at this stage try and create users
    if(count($errors) == 0){
        // create the user row
        $user_key = uniqid('USER:', true);
        $password_hash = md5($password);
        $stmt = $mysqli->prepare("INSERT INTO users (`display_name`, `email`, `password`, `key`, `access_token`, `created`) VALUES (?, ?, ?, ?,?, now())");
        error_log($mysqli->error);
        $stmt->bind_param("sssss", $display_name, $email, $password_hash, $user_key, $access_token);
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
            $out['accessToken'] = $access_token;
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
        
        // create an access token they can use to view
        // their own records via their phone
        // make it random and URL safe 
        $access_token = authentication_generate_access_token();
        
        // save it in the db
        $stmt2 = $mysqli->prepare("UPDATE users SET access_token = ? WHERE `key` = ?");
        echo $mysqli->error;
        $stmt2->bind_param('ss', $access_token, $user_key);
        $stmt2->execute();
        
        // pass it back for them to use
        $out['accessToken'] = $access_token;
        
    }else{
        $out['success'] = false;
    }
    
    return_json($out);
    
}

function authentication_generate_access_token(){
    return str_replace( '%', '', urlencode(openssl_random_pseudo_bytes(20)));
}

?>