<?php
    include_once('auth.php');
    require_once('../config.php');
?>
<htm>
    <title>Ten Breaths Manager</title>
    <style>
        img{
            max-height: 50px;
            max-width: 100px;
        }
        table{
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid black;
        }
        tr:hover {background-color: #f5f5f5}
        th, td {
            padding: 5px;
            text-align: left;
        }
        th {
            text-align: left;
            background-color: #4CAF50;
            color: white;
        }
        td{
            vertical-align: top;
        }
        .fix-width{
            width: 80px;
        }
    </style>
</htm>
<body>
    <div>
        <strong>Ten Breaths Manager: </strong>
        <a href="index.php">Submissions</a>
        |
        <a href="people.php">People</a>
        |
        <a href="email.php">Email</a>
        |
        You are: <?php echo $user_name ?> (<a href="index.php?auth_log_out=true">Log Out</a>)
    </div>