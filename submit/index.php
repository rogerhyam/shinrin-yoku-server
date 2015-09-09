
<?php

    require_once('../config.php');
    
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Max-Age: 1000');
    
    if($_SERVER['REQUEST_METHOD'] != 'POST'){
       render_test_form();
       exit();
    }

    // are we uploading a file?
    if( isset($_FILES["file"]) ){

        // load the survey by the id of the filename
        $survey_id = preg_replace('/\\.[^.\\s]{3,4}$/', '', $_FILES["file"]["name"]);
        error_log($survey_id);
        $response = $mysqli->query("SELECT created FROM submissions WHERE survey_id = '$survey_id'");
        
        // fail if we can't find the survey
        if($response->num_rows < 1){
            error_log("Can't find survey for $survey_id");
            header("HTTP/1.1 409 Conflict");
            echo "Sorry. Failed to add photo to survey. Can't find survey the survey in the db.";
            exit();
        }
        
        $row = $response->fetch_assoc();
        $survey = json_decode($row['survey_json']);
        $date_path = str_replace('-', '/', substr($row['created'], 0,10) );
        $dir_path = '../data/' . $date_path;
        error_log($dir_path);
        if (!is_dir($dir_path)) {
            mkdir($dir_path, 0777, true);
        }
        
        if(move_uploaded_file($_FILES["file"]["tmp_name"], $dir_path . '/' . $_FILES["file"]["name"])){
            $photo_path = $date_path . '/' . $_FILES["file"]["name"]; // stored in db
            $mysqli->query("UPDATE submissions SET photo = '$photo_path' WHERE survey_id = '$survey_id'");
        }
        
        // fixme: check file size
        // fixme: check file type
        // fixme: check application id
    
        echo 0;
        
    }
    
    if(isset($_POST['survey'])){
        
        $survey =   json_decode($_POST['survey']);
        $surveyor = json_decode($_POST['surveyor']);
        $api_key = $_POST['api_key'];
        
        error_log(print_r($survey, true));
        error_log(print_r($surveyor, true));
        
        // insert the submission
        $user_id = get_user_id($surveyor->email, $surveyor->display_name);
        $api_key_id = get_api_key_id($user_id, $api_key);
        $survey_id = $survey->id; // fixme - sanitize variable before e.g. 5c102144-4448-4160-8f26-31cd64ac11c6
        $survey_json = json_encode($survey); // re-encode to help prevent sql injection
        $surveyor_json = json_encode($surveyor); // re-encode to help prevent sql injection
        
        $sql = "INSERT INTO submissions (survey_id, survey_json, surveyor_json, api_key_id, created) VALUES ('$survey_id', '$survey_json', '$surveyor_json', $api_key_id, now() )";
        
        if (!$mysqli->query($sql)) {
            error_log($sql);
            error_log($mysqli->error);
        }
        $mysqli->query($sql);
        
        echo 0;
        
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


function get_user_id($email, $display_name){
    
     global $mysqli;
    
    // have we seen this user before
    $email = trim($email);
    $stmt = $mysqli->prepare("SELECT id, email, display_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 0){
        
        error_log('Not Seen user before');
        
        // we haven't seen this user before - can we create them
        if(display_name_available($display_name)){
            error_log("About to CREATE user");
            $stmt = $mysqli->prepare("INSERT INTO users (display_name, email, created) VALUES (?, ?, now())");
            $stmt->bind_param("ss", $display_name, $email);
            $stmt->execute();
            if($stmt->affected_rows != 1){
                error_log($mysqli->error);
                $stmt->close();
                return false;
            }else{
                $id = $stmt->insert_id;
                error_log("CREATED user: " . $id);
                $stmt->close();
                return $id;
            }
           
        }else{
            display_name_clash($display_name);
            return false;
        }
        
    }else{
        
        // we have seen them before        
        $stmt->bind_result($db_id, $db_email, $db_display_name);
        $stmt->fetch();
        
        // have they changed their user name?
        if($db_display_name != $display_name){
            
            if(display_name_available($display_name)){
                // OK so update their display_name and return the id
                error_log("About to UPDATE user");
                $stmt = $mysqli->prepare("UPDATE users SET display_name = ? WHERE id = ?");
                $stmt->bind_param("si", $display_name, $db_id);
                $stmt->execute();
                error_log("UPDATEd  user");
                return $db_id;
            }else{
                // bad stuff so give up
                display_name_clash($display_name);
                return false;
            }
            
        }else{
            // nothing changed so return their id
            return $db_id; 
        }
        
    }
    
    $stmt->close();
    
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

<?php echo display_name_available($_GET['dn']); ?>

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