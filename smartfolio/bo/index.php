<?php
include 'assets/inc/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php include 'assets/inc/head.php'; ?>
    <title>SmartFolio</title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_smartfolio.php';

    // PORTFOLIO LIST
    foreach (Portfolio::UserOpenList() as $port) {
        App::Debug($port);
    }
    ?>
</body>
</html>