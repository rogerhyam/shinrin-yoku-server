<?php
    $video_id = "3_1LlnHLwiw";
    $video_uri = "https://youtu.be/$video_id";
    header( "Location: $video_uri" ) ;
    
?>
<html>
    <head>
        <title>Ten Breaths Map Video Tutorial</title>
    </head>
    <body>
         Redirecting to <a href="<?php echo $video_uri ?>">YouTube video</a>.   
    </body>
</html>
