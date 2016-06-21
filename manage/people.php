<?php
    include_once('top.php');
?>
    <h1>People</h1>
    
   <table>
        <tr>
            <th>ID</th>
            <th>Created</th>
            <th>Name</th>
            <th>Email</th>
            <th>Validated</th>
            <th>Submissions</th>
        </tr>
    
<?php
    
    $sql = "SELECT u.*, count(s.id) as submission_count FROM users AS u LEFT JOIN submissions AS s ON u.id = s.user_id GROUP BY u.id";
    $result = $mysqli->query($sql);
    
    while($row = $result->fetch_assoc()){
        
        $user_id = $row['id'];
        $display_name = $row['display_name'];
        $email = $row['email'];
        
        echo '<tr>';
        
        // user id
        echo '<td>';
        echo $row['id'];
        echo '</td>';
        
        // date
        echo '<td>';
        echo $row['created'];
        echo '</td>';
        
        // dipslay name
        echo '<td>';
        echo $display_name;
        echo '</td>';
        
        echo '<td>';
        echo "<a name=\"user_id_$user_id\" href=\"mailto:$email\">$email</a>";
        echo '</td>';
        
        // validate
        echo '<td>';
        if($row['validated']){
            echo "<a href=\"verb_user_unvalidate.php?user_id=$user_id\">Validated</a>";
        }else{
            echo "<a href=\"verb_user_validate.php?user_id=$user_id\">Not Validated</a>";
        }
        echo '</td>';
        
        // number of submissions
        echo '<td>';
        if($row['submission_count']){
            echo "<a href=\"index.php?user_id=$user_id\">". $row['submission_count'] . "</a>";
        }else{
            echo "0 - <a href=\"verb_user_delete.php?user_id=$user_id\">Delete User</a>";
        }
        
        echo '</td>';
        
        echo '</tr>';
        
    }

?>

    </table>


<?php
    include_once('top.php');
?>