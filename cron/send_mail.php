<?php
    require_once('config.php');
    
    // this is called by cron every once in a while and sends the
    // emails queued in the email_queue table
    
    // fetch a list of up to 100 not sent
    $stmt = $mysqli->prepare('SELECT id, to_address, to_name, subject, body FROM email_queue WHERE success IS NULL ORDER BY created DESC LIMIT 100');
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows == 0) exit(); // nothing to do

    // got stuff so bind variables and work through them
    $stmt->bind_result($queue_id, $to_address, $to_name, $subject, $body);
    while($stmt->fetch()){

            $mysqli->query("UPDATE email_queue SET attempt = now(), attempt_count = attempt_count + 1 WHERE id = $queue_id");
            $mail = get_mail();
            $mail->setFrom('r.hyam@rbge.org.uk', 'Ten Breaths Map');
            $mail->addAddress($to_address, $to_name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            
            if(!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
                $stmt2 = $mysqli->prepare('UPDATE email_queue SET error = ? WHERE id = ?');
                $stmt2->bind_param('si', $mail->ErrorInfo, $queue_id);
                $stmt2->execute();
            } else {
                $mysqli->query("UPDATE email_queue SET success = now() WHERE id = $queue_id");
                echo "Email sent to: $to_address \n";
            }
    
        
    }
    
    
    function get_mail(){
    
        // we keep the smtp config details in a file
        // outside the web root and github for security
        include("../tenbreaths_email_config.php");
    
        $mail = new PHPMailer;
    
        //$mail->SMTPDebug = 3;                       // Enable verbose debug output
        $mail->isSMTP();                              // Set mailer to use SMTP
        $mail->Host = $email_config['host'];          // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                       // Enable SMTP authentication
        $mail->Username = $email_config['username'];  // SMTP username
        $mail->Password = $email_config['password'];  // SMTP password
        $mail->SMTPSecure = 'tls';                    // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $email_config['port'];          // TCP port to connect to
        
        if(isset($email_config['bcc']) && $email_config['bcc']){
            $mail->addBCC($email_config['bcc']);
        }
    
        return $mail;
    
  }
    
?>