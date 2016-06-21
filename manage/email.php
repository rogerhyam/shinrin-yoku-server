<?php
    include_once('top.php');
?>
    <a style="display: block; float: right;" href="../cron/send_mail.php">Send email now</a>
    <h1>Email</h1>
   <table>
    <tr>
        <th>ID</th>
        <th>Created</th>
        <th>Kind</th>
        <th>To Name</th>
        <th>To Address</th>
        <th>Attempt</th>
        <th>Success</th>
        <th>Attempt Count</th>
        <th>Delete</th>
        <th>Error</th>
    </tr>
<?php
    
    $sql = "SELECT * FROM email_queue ORDER BY created DESC LIMIT 1000";
    $result = $mysqli->query($sql);
    
    while($row = $result->fetch_assoc()){

        echo '<tr>';
        
        // id 
        echo '<td>';
        echo $row['id'];
        echo '</td>';
        
        // date
        echo '<td>';
        echo $row['created'];
        echo '</td>';
        
        // kind
        echo '<td>';
        echo $row['kind'];
        echo '</td>';
        
        // to_name
        echo '<td>';
        echo $row['to_name'];
        echo '</td>';
        
        // to_address
        echo '<td>';
        echo $row['to_address'];
        echo '</td>';
        
        // attemopt
        echo '<td>';
        echo $row['attempt'];
        echo '</td>';
        
        // success
        echo '<td>';
        echo $row['success'];
        echo '</td>';
        
        // attempt count
        echo '<td>';
        echo $row['attempt_count'];
        echo '</td>';

        echo '<td>';
        echo "<a href=\"verb_email_delete.php?email_id={$row['id']}\">Delete</a>";
        echo '</td>';
    
        echo '<td class="fix-width">';
        echo $row['error'];
        echo '</td>';
        
        echo '</tr>';
        
    }


?>    
    </table>

<?php
    include_once('top.php');
?>