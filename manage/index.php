<?php
    include_once('top.php');
?>
    <h1>Submissions
<?php
    if(@$_GET['user_id']){
        echo " for User ID : " . $_GET['user_id'];
    }  
?>
    </h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Started</th>
            <th>Submitted</th>
            <th>User</th>
            <th>Photo</th>
            <th>Text</th>
            <th>GMap</th>
            <th>Visibility</th>
            <th>Public</th>
        </tr>
    
<?php
    
    $sql = "SELECT 
        s.id as submission_id, s.created as submitted, s.public, u.id as user_id, s.photo, s.survey_json, s.moderated, u.display_name
        FROM submissions as s JOIN `users` as u ON s.user_id = u.id";
        
    if(@$_GET['user_id']){
        $sql .= " WHERE  u.id = " . $_GET['user_id'];
    }    
    
    $sql .= ' ORDER BY s.id DESC LIMIT 1000';
    
    $result = $mysqli->query($sql);
    
    while($row = $result->fetch_assoc()){
        
        $data = json_decode($row['survey_json']);
        $id = $row['submission_id'];
        
        echo '<tr>';
        
        // submission id
        echo '<td>';
        echo '<a name="submission-'.$id.'" target="ten-view" href="../survey-' . $data->id . '">';
        echo $row['submission_id'];
        echo '</a>';
        echo '</td>';
        
        // started
        echo '<td>';
        $started = new DateTime('@'.round($data->started/1000));
        echo $started->format(DATE_ATOM);
        echo '</td>';
        
        // submitted
        echo '<td>';
        echo $row['submitted'];
        echo '</td>';
        
        echo '<td>';
        $user_id = $row['user_id'];
        $display_name = $row['display_name'];
        echo "<a href=\"people.php#user_id_$user_id\">$display_name</a>";
        echo '</td>';
        
        // submission image
        echo '<td>';
        if($row['photo']){
            $photo_path = '../data/' . $row['photo'];
            echo "<a href=\"$photo_path\"><image src=\"$photo_path\" /></a>";
        }else{
            $photo_path = '';
            echo '-';
        }
        echo '</td>';
        
        echo '<td>';
        echo $data->textComments;
        echo '</td>';

        echo '<td>';
        $map_uri = "https://www.google.com/maps?z=14&mrt=yp&t=k&q={$data->geolocation->latitude}+{$data->geolocation->longitude}";
        echo "<a target=\"ten-map\" href=\"$map_uri\">Map</a>";
        echo '</td>';
        
        echo '<td>';
        if($row['public']){
            echo "<a href=\"verb_submission_hide.php?id=$id\">Public</a>";   
        }else{
            echo "<a href=\"verb_submission_unhide.php?id=$id\">Hidden</a>";
        }
        echo '</td>';
        
        echo '<td>';
        echo "<a href=\"verb_submission_delete.php?id=$id&photo_path=$photo_path\">Delete</a>";   
        echo '</td>';
        
        //echo '<td>';
        //echo var_dump($data);
        // echo '</td>';
        
        echo '</tr>';
        
    }

?>

    </table>

<?php
    include_once('top.php');
?>