<?php
    
    require_once('config.php');
    
    $validation_token = @$_GET['t'];
    
    // if there isn't a token just
    // silently redirect them to the map
    if(!$validation_token){
        header('Location: /', true, 303);
        exit();
    }
    
    // look up where the token applies to
    $stmt = $mysqli->prepare('SELECT id FROM users WHERE validation_token = ?');
    $stmt->bind_param("s", $validation_token);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 1){
        $stmt->bind_result($user_id);
        $stmt->fetch();

        // tick the validated box
        // blank the valitation_token field
        $mysqli->query("UPDATE users SET validation_token = NULL, validated = 1 WHERE id = $user_id");
        
        $_SESSION['message_title'] = "Email Address Confirmed";
        $_SESSION['message_body'] = "<p>Thank you for validating your email address.</p>";
        $_SESSION['message_body'] .= "<p>Your contributions can now appear on the map.</p>";
        
    }else{
        $_SESSION['message_title'] = "Email Address Confirmation Failed";
        $_SESSION['message_body'] = "<p>The verification token wasn't recognised.
            Perhaps it has already been used or the URL is corrupted in the email.</p>";
        $_SESSION['message_body'] .= "<p>Please try again.</p>";
    }


    header('Location: /?pp=message-page', true, 303);
    
    echo "Redirecting ...";
    

?>