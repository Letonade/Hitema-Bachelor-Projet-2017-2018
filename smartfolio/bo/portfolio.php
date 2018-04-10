<?php
include 'assets/inc/init.php';

$portfolio = new Portfolio($_GET['port']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php include 'assets/inc/head.php'; ?>
    <title>Portefeuille - <?php echo $portfolio->customer->infos['name']; ?></title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_portfolio.php';
    ?>
    <main id="main">
        <?php App::DisplayMessages(); ?>
        <div id="indexes">
            <h3>Monnaies index:</h3>
        </div>
        <div id="investments">
            <h3>Investissements:</h3>
        </div>
    </main>
</body>
</html>