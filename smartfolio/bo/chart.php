<?php
include 'assets/inc/init.php';

$pair = new Pair($_GET['pair']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php include 'assets/inc/head.php'; ?>

    <link rel="stylesheet" href="assets/style/chart.min.css">
    <script src="<?php echo PROTOCOL; ?>://d3js.org/d3.v4.min.js"></script>
    <script src="../assets/rsc/techan-0.8.0/techan.min.js" charset="utf-8"></script>
    <script src="assets/js/chart.js" charset="utf-8"></script>
    <title><?php echo $pair->symbol; ?></title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_smartfolio.php';
    ?>
    <div id="chart"
    data-pair="<?php echo $pair->infos['id']; ?>"
    data-symbol="<?php echo $pair->symbol; ?>"
    data-index="<?php echo $pair->index->infos['symbol']; ?>"></div>
</body>
</html>