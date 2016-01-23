<?php
    // email confirming login and issuing a token.
?>
<html>
    <head>
        <title>Login Confirmation</title>
    </head>
    <body>
        <h1>Ten Breaths Map: Login Confirmation</h1>
        <p>Hi <?php echo $display_name ?></p>
        <p>This is to confirm that you logged in to Ten Breaths from the app.</p>
        <h2>Access Link</h2>
        <p>You can use the link below to access a personal view of the map
            that allows you to see all your contributions
            <strong>including the ones you chose to hide.</strong>
        </p>
        <p><strong>Access Link:</strong><?php echo $access_link ?></p>
        <p>
            Anyone who has this link will have the same access so only share it 
            with people who you don't mind seeing your hidden contributions.
        </p>
        <p>For security reasons a replacement access link is created 
            and emailed to you each time you log in from the app and the 
            old link stops working.
        </p>
    </body>
</html>