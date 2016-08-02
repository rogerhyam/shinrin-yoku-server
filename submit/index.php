
<?php

    require_once('../config.php');
    
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Max-Age: 1000');
    
    if($_SERVER['REQUEST_METHOD'] != 'POST'){
       render_test_form();
       exit();
    }


    if(isset($_POST['survey'])){
        
        $survey_json = $_POST['survey'];
        $survey = json_decode($survey_json);
        $survey_key = $survey->id;
        $device_key = $survey->device_key;
        $user_key = $survey->user_key;
        $public = $survey->public;
        $latitude = $survey->geolocation->latitude;
        $longitude = $survey->geolocation->longitude;
        $accuracy = $survey->geolocation->accuracy;
        $started = $survey->started; // unix style timestamp number
        
        $user_id = false;
        
        error_log(print_r($survey, true));

        $stmt = $mysqli->prepare("SELECT id FROM users WHERE `key` = ? ");
        $stmt->bind_param("s", $user_key);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
        
        // only save the survey if the user has an id
        if($user_id){
    
            $stmt = $mysqli->prepare("INSERT INTO submissions 
                                            (survey_key, survey_json, device_key, user_id, public, started, latitude, longitude, accuracy, created)
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,now() )");
            $stmt->bind_param("ssssiiddd", $survey_key, $survey_json, $device_key, $user_id, $public, $started, $latitude, $longitude, $accuracy);
            $stmt->execute();
            
            error_log('started: ' . $started);
            error_log($stmt->error);
            
            if($stmt->affected_rows != 1){
                header("HTTP/1.1 500 Internal Error");
                echo "Unable to insert survey.";
            }else{
                echo 0;
            }
       
        }else{
            // there is no account by that name
            header("HTTP/1.1 403 Forbidden");
            echo "The user key provided isn't recognised.";
            exit();
        }
        
        // process the attached file - there always is one!
        // but we ignore the RED_DOT.png place holder image
        if($_FILES["file"]["name"] != 'RED_DOT.png'){

            $date_path = (new DateTime())->format('Y/m/d');
            $dir_path = '../data/' . $date_path;
            error_log($dir_path);
            if (!is_dir($dir_path)) {
                mkdir($dir_path, 0777, true);
            }
            
            if(move_uploaded_file($_FILES["file"]["tmp_name"], $dir_path . '/' . $_FILES["file"]["name"])){
                $photo_path = $date_path . '/' . $_FILES["file"]["name"]; // stored in db
                $mysqli->query("UPDATE submissions SET photo = '$photo_path' WHERE survey_key = '$survey_key'");
            }
            
        }
        
        // send a notification email to me - I want to know!
        ob_start();
        include('../email_templates/monitor.php');
        $body = ob_get_contents();
        ob_end_clean();
        enqueue_email('monitor', 'roger@hyam.net', 'Monitor', 'Ten Breaths Map Monitor', $body);
    
    }
    
    
    // if this is an authentication request then deal with it 
    // using included file
    if(isset($_POST['authentication'])){
        
        require_once('authentication.php');
        
        $function_name = 'authentication_' . $_POST['authentication'];
        
        if(function_exists($function_name)){
            $function_name();
        }
        
    }
    
function get_api_key_id($user_id, $api_key){
    
    global $mysqli;
    
    error_log("Api key: " . $api_key);
    error_log("user id: " . $user_id);
    
    $stmt = $mysqli->prepare("SELECT id FROM api_keys WHERE `key` = ? AND `user_id` = ? ");
    if(!$stmt){
        error_log($mysqli->error);
    }
    
    $stmt->bind_param("si", $api_key, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 0){
    
        // the key doesn't exist yet for this user so create it
        $stmt = $mysqli->prepare("INSERT INTO api_keys (`user_id`, `key`, `created`) VALUES (?, ?, now())");
        $stmt->bind_param("is", $user_id, $api_key);
        $stmt->execute();
        if($stmt->affected_rows != 1){
            error_log($mysqli->error);
            $stmt->close();
            return false;
        }else{
            $id = $stmt->insert_id;
            $stmt->close();
            return $id;
        }

    }else{
        
        // The key combination exists        
        $stmt->bind_result($db_id);
        $stmt->fetch();
        return $db_id;
        
    }
    
}

// return some kind of warning that 
// we can't save with a clash of display_names
function display_name_clash($display_name){
    header("HTTP/1.1 409 Conflict");
    echo "The display name '$display_name' is already in use. Please pick another.";
    exit();
}

function display_name_available($display_name){
    
    global $mysqli;
    
    $stmt = $mysqli->prepare("SELECT count(*) FROM users WHERE display_name = ?");
    $stmt->bind_param("s", $display_name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    if($count > 0){
        error_log($display_name . " - Not available");
        return false;
    }else{
        error_log($display_name . " - Available");
        return true;
    }
    
}    
 
function render_test_form(){
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ten Breaths Submit</title>
    <script src="https://code.jquery.com/jquery-2.1.4.js" type="text/javascript" charset="utf-8"></script>
    <script>
        $( document ).ready(function() {
            $('#ajax-test').on('click', function(){
                $.post(
                    'index.php',
                    "survey=banana&surveyor=cake",
                    function(data){
                        console.log(data);
                        alert('Data saved');
                    }
                );
            });
        });
    </script>
</head>
<body>

<form action="index.php" method="POST" enctype="multipart/form-data">
    <h2>Select image to upload</h2>
    <input type="file" name="file" id="file"><br/>
    <input type="submit" value="Upload Image" name="submit">
</form>
<hr/>
<form action="index.php" method="POST" enctype="multipart/form-data">
    <h2>data to upload</h2  >
    Survey: <input type="text" name="survey" id="survey"/><br/>
    Surveyor: <input type="text" name="surveyor" id="surveyor"/><br/>
    <input type="submit" value="Submit" name="submit">
</form>
<hr/>
<button id="ajax-test">Ajax Test</button>


</body>
</html>

<?php
}
?>