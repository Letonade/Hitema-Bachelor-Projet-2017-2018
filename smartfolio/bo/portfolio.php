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
            foreach ($portfolio->GetAccumulators() as $inv) {
                echo '<div class="accumulator"><a href="investment.php?port=' . $portfolio->infos['id'] . '&inv=' . $inv->currency->infos['id'] . '">';
                echo '  <h4>' . $inv->currency->infos['name'] . '</h4>';
                echo '  <p>' . $inv->GetBalance() . ' <b>' . $inv->currency->infos['symbol'] . '</b></p>';
                echo '  <div class="rois">';
                foreach ($portfolio->GetAccumulators() as $acc) {
                    if ($inv->currency->infos['id'] != $acc->currency->infos['id']) {
                        echo '<p>' . $acc->currency->infos['symbol'] . '</p>';
                        $roi_class = $inv->GetDelta($acc->currency) >= 0 ? 'delta_up' : 'delta_down';
                        echo '<p class="' . $roi_class . '">' . $inv->GetDelta($acc->currency) . ' %</p>';
                    }
                }
                echo '  </div>';
                echo '</a></div>';
            }
            ?>
        </div>
        <div id="investments">
            <h3>Investissements:</h3>
            <?php
            foreach ($portfolio->GetInvestments() as $inv) {
                echo '<div class="investment"><a href="investment.php?port=' . $portfolio->infos['id'] . '&inv=' . $inv->currency->infos['id'] . '">';
                echo '  <h4>' . $inv->currency->infos['name'] . '</h4>';
                echo '  <p>' . $inv->GetBalance() . ' <b>' . $inv->currency->infos['symbol'] . '</b></p>';
                echo '  <div class="rois">';
                foreach ($portfolio->GetAccumulators() as $acc) {
                    echo '<p>' . $acc->currency->infos['symbol'] . '</p>';
                    $roi_class = $inv->GetDelta($acc->currency) >= 0 ? 'delta_up' : 'delta_down';
                    echo '<p class="' . $roi_class . '">' . $inv->GetDelta($acc->currency) . ' %</p>';
                }
                echo '  </div>';
                echo '</a></div>';
            }
            ?>
        </div>
    </main>
</body>
</html>