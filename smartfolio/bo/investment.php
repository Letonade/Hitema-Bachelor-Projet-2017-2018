<?php
include 'assets/inc/init.php';
// GET PORTFOLIO
try {
    $portfolio = new Portfolio($_GET['port']);
    $investment = Investment::Investment($portfolio->infos['id'], $_GET['inv']);
    $pair = new Pair($investment->DefaultPair());
} catch (\Exception $e) {
    App::Respond('Portefeuille', $e->getMessage(), true);
}

if (isset($_POST['token']) && User::CheckToken($_POST['token'])) {
    // ADD TX
    if (isset($_POST['new_tx'])) {
        $new_tx = $portfolio->NewTx([
            'tx_type'                      => $_POST['tx_type'],
            'tx_transfer_curr_id'          => $investment->currency->infos['id'],
            'tx_transfer_exchange_id_from' => $_POST['tx_transfer_exchange_id_from'],
            'tx_transfer_exchange_id_to'   => $_POST['tx_transfer_exchange_id_to'],
            'tx_pair_id'                   => $_POST['tx_pair_id'],
            'tx_price'                     => $_POST['tx_price'],
            'tx_fee_amount'                => $_POST['tx_fee_amount'],
            'tx_fee_type'                  => $_POST['tx_fee_type'],
            'tx_amount'                    => $_POST['tx_amount'],
            'tx_date'                      => $_POST['tx_date'],
            'tx_hour'                      => $_POST['tx_hour']
        ]);
        App::Respond(
            'Nouvelle transaction',
            $new_tx[0] ? null : $new_tx[1]
        );
        if ($new_tx[0] && $new_alerts[0]) {
            unset($_POST);
        }
    }
    if (isset($_POST['new_alerts'])) {
        $new_alerts = $portfolio->Newalerts([
            'user_id'               => $portfolio->agent->infos['id'],
            'acc_port_id'           => $portfolio->infos['id'],
            'acc_curr_id'           => $investment->currency->infos['id'],
            'investment_type'       => $investment->infos['type'],
            'alerts_value'          => $_POST['alerts_value'],
            'alerts_comparator'     => $_POST['alerts_comparator'],
            'alerts_type'           => $_POST['alerts_type']

        ]);
        App::Respond(
            'Nouvelle alerte',
            $new_alerts[0] ? null : $new_alerts[1]
        );
        if ($new_tx[0] && $new_alerts[0]) {
            unset($_POST);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php include 'assets/inc/head.php'; ?>

    <link rel="stylesheet" href="assets/style/inv_chart.min.css">
    <script src="https://d3js.org/d3.v4.min.js"></script>
    <script src="../assets/rsc/techan-0.8.0/techan.min.js" charset="utf-8"></script>
    <script src="assets/js/investment.js" charset="utf-8"></script>
    <link rel="stylesheet" href="assets/style/investment.min.css">
    <script src="assets/js/tx_form.js" charset="utf-8"></script>
    <script src="assets/js/alerts_form.js" charset="utf-8"></script>
    <script src="assets/js/response.js" charset="utf-8"></script>
    <title>Portefeuille - <?php echo $portfolio->customer->infos['name'] ?? 'erreur'; ?></title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_portfolio.php';
    ?>
    <!-- FORM FOR TX-->
    <div id="form_tx" class="<?php echo isset($_POST['new_tx']) ? '' : 'hidden' ?>">
        <form class="form_new tx_form" action="investment.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
            <h3><i class="fas fa-plus"></i> Ajouter une transaction</h3>
            <button type="button" name="button"><i class="far fa-times-circle">X</i></button>
            <!-- SELECT TX TYPE -->
            <label for="tx_type">Type de transaction:</label>
            <select name="tx_type">
                <option hidden>--- Choix ---</option>
                <option value="deposit" <?php echo ($_POST['tx_type'] ?? '') == 'deposit' ? 'selected' : ''; ?>>Dépôt</option>
                <option value="buy" <?php echo ($_POST['tx_type'] ?? '') == 'buy' ? 'selected' : ''; ?>>Achat</option>
                <option value="transfer" <?php echo ($_POST['tx_type'] ?? '') == 'transfer' ? 'selected' : ''; ?>>Transfert</option>
                <option value="sell" <?php echo ($_POST['tx_type'] ?? '') == 'sell' ? 'selected' : ''; ?>>Vente</option>
                <option value="withdraw" <?php echo ($_POST['tx_type'] ?? '') == 'withdraw' ? 'selected' : ''; ?>>Retrait</option>
            </select>
            <label for="tx_amount">Montant</label>
            <div class="input_prefix" id="tx_amount">
                <div class="prefix"><i class="fab fa-bitcoin"></i><span><?php echo $investment->currency->infos['symbol']; ?></span></div>
                <input type="number" name="tx_amount" value="<?php echo $_POST['tx_amount'] ?? "0"; ?>" min="0" step='0.000000000000000001'>
            </div>
            <datalist id="tx_tf_to_list">
                <?php
                foreach (Exchange::FullList() as $exchange) {
                    echo '<option>' . $exchange->infos['name'] . '</option>';
                }
                ?>
            </datalist>
            <datalist id="tx_pair_list">
                <?php
                foreach (Pair::FindByCurrency($investment->currency, 'currency') as $pair) {
                    echo '<option>' . $pair->symbol . '</option>';
                }
                ?>
            </datalist>
            <!-- SPECIFIC FIELDS -->
            <div id="tx_fields">
                <!-- Exchange from -->
                <label class="hidden" data-tx='["transfer", "withdraw"]' for="tx_transfer_exchange_id_from">Depuis:</label>
                <input class="hidden" data-tx='["transfer", "withdraw"]' type="text" name="tx_transfer_exchange_id_from" value="<?php echo $_POST['tx_transfer_exchange_id_from'] ?? ''; ?>" list="tx_tf_to_list">
                <!-- Exchange to -->
                <label class="hidden" data-tx='["deposit", "transfer"]' for="tx_transfer_exchange_id_to">Vers:</label>
                <input class="hidden" data-tx='["deposit", "transfer"]' type="text" name="tx_transfer_exchange_id_to" value="<?php echo $_POST['tx_transfer_exchange_id_to'] ?? ''; ?>" list="tx_tf_to_list">
                <!-- Price -->
                <label class="hidden" data-tx='["buy", "sell"]' for="tx_price">Prix:</label>
                <div class="hidden input_prefix" data-tx='["buy", "sell"]' id="tx_price">
                    <div class="prefix"><i class="fas fa-dollar-sign"></i><span></span></div>
                    <input type="number" name="tx_price" value="<?php echo $_POST['tx_price'] ?? "0"; ?>" min="0" step='0.000000000000000001'>
                </div>
                <!-- Pair -->
                <label class="hidden" data-tx='["buy", "sell"]' for="tx_pair_id">Paire:</label>
                <input class="hidden" data-tx='["buy", "sell"]' type="text" name="tx_pair_id" value="<?php echo $_POST['tx_pair_id'] ?? ''; ?>" list="tx_pair_list">
                <!-- Fees -->
                <label class="hidden l15" data-tx='["buy", "transfer", "sell", "withdraw"]' for="tx_fee_amount">Montant des frais:</label>
                <label class="hidden s15" data-tx='["buy", "transfer", "sell", "withdraw"]' for="tx_fee_type">Type de frais:</label>
                <input class="hidden l15" data-tx='["buy", "transfer", "sell", "withdraw"]' type="number" name="tx_fee_amount" value="<?php echo $_POST['tx_fee_amount'] ?? "0"; ?>" min="0" step='0.000000000000000001'>
                <select class="hidden s15" data-tx='["buy", "transfer", "sell", "withdraw"]' name="tx_fee_type">
                    <option hidden>--- Choix ---</option>
                    <option value="fixed_currency">[<?php echo $investment->currency->infos['symbol']; ?>]</option>
                    <option disabled value="fixed_index">[Index]</option>
                    <option value="percent_currency">%<?php echo $investment->currency->infos['symbol']; ?></option>
                    <option disabled value="percent_index">%Index</option>
                </select>
            </div>
            <!-- Mandatory info -->
            <label for="tx_date">Date:</label>
            <input type="date" name="tx_date" value="<?php echo $_POST['tx_date'] ?? (new DateTime())->format('Y-m-d'); ?>" max="<?php echo (new DateTime())->format('Y-m-d'); ?>">
            <label for="tx_hour">Heure:</label>
            <input type="time" name="tx_hour" value="<?php echo $_POST['tx_hour'] ?? (new DateTime())->format('H:i'); ?>">
            <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
            <input type="submit" name="new_tx" value="Nouvel investissement" <?php echo isset($_POST['tx_type']) ? '' : 'disabled'; ?>>
        </form>
    </div>
    <!--FORM FOR ALERTS -->
    <div id="form_alerts" class="<?php echo isset($_POST['new_alerts']) ? '' : 'hidden' ?>">
        <form class="form_new alerts_form" action="investment.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
            <h3><i class="fas fa-plus"></i> Ajouter une alerte</h3>
            <button type="button" name="button"><i class="far fa-times-circle">X</i></button>
            <!-- SELECT alerts TYPE -->
            <label for="alerts_type">Type d'alerte:</label>
            <select name="alerts_type">
                <option hidden>--- Choix ---</option>
                <option value="marge" <?php echo ($_POST['alerts_type'] ?? '') == 'marge' ? 'selected' : ''; ?>>Sur une marge</option>
                <option value="fixe" <?php echo ($_POST['alerts_type'] ?? '') == 'fixe' ? 'selected' : ''; ?>>Sur une valeur fixe</option>
            </select>
            <!-- SPECIFIC FIELDS -->
            <div id="alerts_fields">
            <!-- Comparateur -->
            <label class="hidden" data-alerts='["marge", "fixe"]' for="alerts_comparator">Comparateur :</label>
            <select name="alerts_comparator" class="hidden input_prefix" data-alerts='["marge", "fixe"]' id="alerts_comparator">
                <option hidden>--- Choix ---</option>
                <option value="==" <?php echo ($_POST['alerts_comparator'] ?? '') == '"=="' ? 'selected' : ''; ?>>==</option>
                <option value=">=" <?php echo ($_POST['alerts_comparator'] ?? '') == '">="' ? 'selected' : ''; ?>>>=</option>
                <option value=">"  <?php echo ($_POST['alerts_comparator'] ?? '') == '">" ' ? 'selected' : ''; ?>>></option>
                <option value="<=" <?php echo ($_POST['alerts_comparator'] ?? '') == '"<="' ? 'selected' : ''; ?>><=</option>
                <option value="<"  <?php echo ($_POST['alerts_comparator'] ?? '') == '"<" ' ? 'selected' : ''; ?>><</option>
            </select>
                <!-- Valeur -->
                <label class="hidden" data-alerts='["marge"]' for="alerts_value">Marge :</label>
                <label class="hidden" data-alerts='["fixe"]' for="alerts_value">Valeur :</label>
                <div class="hidden input_prefix" data-alerts='["marge", "fixe"]' id="alerts_value">
                    <div class="prefix"><i class="fas fa-dollar-sign"></i><span><?php echo $investment->currency->infos['symbol']; ?></span></div>
                    <input type="number" name="alerts_value" value="<?php echo $_POST['alerts_value'] ?? "0"; ?>" min="0" step='0.000000000000000001'>
                </div>
            </div>
            <!-- Submit info -->
            <input type="submit" name="new_alerts" value="Nouvelle alerte" <?php echo isset($_POST['alerts_type']) ? '' : 'disabled'; ?>>
        </form>
    </div>
    <!-- FIN FORM -->
    <?php App::DisplayMessages(); ?>
    <main id="investment">
        <!-- CHART -->
        <div id="data">
            <div id="chart">
                <div class="actions">
                    <h3><i class="far fa-chart-bar"></i> Graphique</h3>
                    <select id="chart_pair">
                        <?php
                        foreach (Pair::FindByCurrency($investment->currency, 'currency') as $chart_pair) {
                            echo '<option value="' . $chart_pair->infos['id'] . '">' . $chart_pair->symbol . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div id="techan_js"
                data-pair="<?php echo $pair->infos['id']; ?>"
                data-symbol="<?php echo $pair->symbol; ?>"
                data-index="<?php echo $pair->index->infos['symbol']; ?>"></div>
            </div>
            <!-- INFOS -->
            <div id="infos">
                <div class="actions">
                    <h3><i class="fas fa-info"></i> Infos</h3>
                </div>
                <div id="balance">Solde: <span><?php echo $investment->GetBalance() . ' ' . $investment->currency->infos['symbol']; ?></span></div>
                <div id="roi">
                    <h3>ROI:</h3>
                    <?php echo $investment->HTML_AccumulatorDelta($portfolio); ?>
                </div>
                <div id="global">
                    <h3>Infos:</h3>
                    <?php echo $investment->HTML_GlobalData($portfolio); ?>
                </div>
            </div>
            <!-- ALERTS -->
            <div id="alerts">
                <div class="actions">
                    <h3><i class="fas fa-bell"></i> Alertes</h3>
                    <button type="button" name="button"><i class="fas fa-plus-circle">+</i></button>
                </div>
                <div id="alert_list"><!--insérer la liste ici--><h3>

                <div class="tx_part">;
                <h4>POLOP</h4>;
                <p class="">pimp</p>;
                </div>';


                Affichage</h3></div>
            </div>
        </div>
        <!-- HISTORY -->
        <div id="history">
            <div class="actions">
                <h3><i class="fas fa-university"></i> Historique de transactions</h3>
                <button type="button" name="button"><i class="fas fa-plus-circle">+</i></button>
            </div>
            <div id="tx_list">
                <?php
                foreach (array_reverse($investment->GetTxHistory()) as $tx) {
                    echo $tx->SumUp();
                }
                ?>
            </div>
        </div>
    </main>
</body>
</html>