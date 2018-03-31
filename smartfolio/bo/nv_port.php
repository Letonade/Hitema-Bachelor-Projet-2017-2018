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
    <title>Nouveau Portfolio</title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_smartfolio.php';
    ?>
    <main id="main">
        <form action="nv_port.php" method="post">
            <h3>Créer un nouveau portfolio:</h3>
            <label for="customer">Client</label>
            <select name="customer">
                
            </select>
        </form>
    </main>
</body>
</html>