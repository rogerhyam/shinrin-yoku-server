<html>
    <head>
        <title>Confirm Email</title>
    </head>
    <body>
        <h1>Ten Breaths Map: Confirm Email</h1>
        <p>Hi <?php echo $display_name ?>,</p>
        <p>
            Welcome to Ten Breaths Map.
            We hope you enjoy recording the places that you find connection 
            with nature.
        </p>
        <p>
            Before your contributions can appear on the public map you need
            to confirm your email address by clicking on the link below
        </p>
        <p><strong>Confirmation Link:</strong>
            <a href="<?php echo $confirmation_link ?>"><?php echo $confirmation_link ?></a>
        </p>
       <p>You can use the link below to access a personal view of the map
            that allows you to see all your contributions
            <strong>including the ones you chose to hide.</strong>
        </p>
        <p><strong>Access Link:</strong>
            <a href="<?php echo $access_link ?>"><?php echo $access_link ?></a>
        </p>
        <p>
            Anyone who has this link will have the same access so only share it 
            with people who you don't mind seeing your hidden contributions.
        </p>
        <p> For security reasons a replacement access link is created 
            and emailed to you each time you log in from the app and the 
            old link stops working.
        </p>
        <p>
            If you have any questions please reply to this email and we will
            do what we can to help.
        </p>
    </body>
</html>