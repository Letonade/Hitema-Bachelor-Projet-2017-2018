<?php
include 'assets/inc/init.php';

// GET PORTFOLIO
try {
    $portfolio = new Portfolio($_GET['port']);
} catch (\Exception $e) {
    App::Respond('Portefeuille', $e->getMessage(), true);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php include 'assets/inc/head.php'; ?>
    <link rel="stylesheet" href="assets/style/portfolio.min.css">
    <title>Portefeuille - <?php echo $portfolio->customer->infos['name'] ?? 'erreur'; ?></title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_portfolio.php';
    ?>
    <main id="main">
        <div id="actions">
            <nav>
                <a href="port_settings.php?port=<?php echo $portfolio->infos['id'] ?>&add_tx"><i class="fas fa-credit-card"></i> Ajouter un investissement</a>
            </nav>
            <form action="portfolio.php" method="get">
                <input type="hidden" name="port" value="<?php echo $portfolio->infos['id']; ?>">
                <input type="text" name="search" value="<?php echo $_GET['search'] ?? ''; ?>">
                <input type="submit" value="Rechercher">
            </form>
        </div>
        <?php App::DisplayMessages(); ?>
        <div id="accumulators">
            <h3>Accumulateurs:</h3>
            <?php
            foreach ($portfolio->accumulators as $acc) {
                $inv = $portfolio->investments[$acc->infos['id']] ?? false;
                echo '<div class="accumulator">';
                echo '  <h4>' . $acc->infos['name'] . '</h4>';
                echo '  <p>' . ($inv['balance'] ?? 0) . ' <b>' . $acc->infos['symbol'] . '</b></p>';
                echo '</div>';
            }
            ?>
        </div>
        <div id="investments">
            <h3>Investissements:</h3>
            <?php
            foreach ($portfolio->investments as $inv) {
                if ($inv['type'] == 'investment') {
                    echo '<div class="investment">';
                    echo '  <h4>' . $inv['currency']->infos['name'] . '</h4>';
                    echo '  <p>' . $inv['balance'] . ' <b>' . $inv['currency']->infos['symbol'] . '</b></p>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </main>
</body>
</html>