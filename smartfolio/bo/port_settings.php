<?php
include 'assets/inc/init.php';

// GET PORTFOLIO
try {
    $portfolio = new Portfolio($_GET['port']);
} catch (\Exception $e) {
    App::Respond('Portefeuille', $e->getMessage(), true);
}

// ACTIONS
if (isset($_POST['token']) && User::CheckToken($_POST['token'])) {

    // ADD ACCUMULATOR
    if (isset($_POST['new_acc'])) {
        $new_accumulator = $portfolio->AddAccumulator($_POST['acc_curr_id']);
        App::Respond(
            'Nouvel accumulateur',
            $new_accumulator[0] ? null : $new_accumulator[1]
        );

        // ADD NEW TRANSACTION
    } elseif (isset($_POST['new_tx'])) {
        $new_tx = $portfolio->FirstTx(array(
            'tx_type'                    => $_POST['tx_type'],
            'tx_transfer_curr_id'        => $_POST['tx_transfer_curr_id'],          // Deposit
            'tx_transfer_exchange_id_to' => $_POST['tx_transfer_exchange_id_to'],
            'tx_pair_id'                 => $_POST['tx_pair_id'],                   // Buy
            'tx_price'                   => $_POST['tx_price'],
            'tx_fee_amount'              => $_POST['tx_fee_amount'],
            'tx_fee_type'                => $_POST['tx_fee_type'],
            'tx_amount'                  => $_POST['tx_amount'],                    // General
            'tx_date'                    => $_POST['tx_date'],
            'tx_hour'                    => $_POST['tx_hour']
        ));
        App::Respond(
            'Nouvelle transaction',
            $new_tx[0] ? null : $new_tx[1]
        );
        if ($new_tx[0]) {
            unset($_POST);
        }
    }
} elseif (isset($_GET['token']) && User::CheckToken($_GET['token'])) {

    // DELETE ACCUMULATOR
    if (isset($_GET['del_acc'])) {
        $del_accumulator = $portfolio->RemoveAccumulator($_GET['del_acc']);
        App::Respond(
            'Suppression de l\'accumulateur',
            $del_accumulator[0] ? null : $del_accumulator[1]
        );
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
    <script src="assets/js/new_tx.js" charset="utf-8"></script>
    <title>Portefeuille - <?php echo $portfolio->customer->infos['name'] ?? 'erreur'; ?></title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_portfolio.php';
    ?>
    <main id="main">
        <?php
        App::DisplayMessages();
        // App::Debug($_POST);
        if (isset($_GET['add_tx'])) {
            ?>
            <form class="form_new form_tx" action="port_settings.php?port=<?php echo $portfolio->infos['id']; ?>&add_tx" method="post">
                <h3>Ajouter une transaction</h3>
                <!-- SELECT TX TYPE -->
                <label for="tx_type">Type de transaction:</label>
                <select name="tx_type">
                    <option hidden>--- Choix ---</option>
                    <option value="deposit" <?php echo ($_POST['tx_type'] ?? '') == 'deposit' ? 'selected' : ''; ?>>Dépôt</option>
                    <option value="buy" <?php echo ($_POST['tx_type'] ?? '') == 'buy' ? 'selected' : ''; ?>>Achat</option>
                </select>
                <label for="tx_amount">Montant</label>
                <div class="input_prefix" id="tx_amount">
                    <div class="prefix"><i class="fab fa-bitcoin"></i><span></span></div>
                    <input type="number" name="tx_amount" value="<?php echo $_POST['tx_amount'] ?? "0"; ?>" min="0" step='0.000000000000000001'>
                </div>
                <!-- DEPOSIT -->
                <div class="tx_type_block <?php echo ($_POST['tx_type'] ?? '') == 'deposit' ? 'selected' : ''; ?>" id="deposit">
                    <label for="tx_transfer_curr_id">Monnaie:</label>
                    <input type="text" name="tx_transfer_curr_id" value="<?php echo $_POST['tx_transfer_curr_id'] ?? ''; ?>" list="tx_curr_list">
                    <datalist id="tx_curr_list">
                        <?php
                        foreach (Currency::FullList() as $currency) {
                            echo '<option>[' . $currency->infos['symbol'] . '] ' . $currency->infos['name'] . '</option>';
                        }
                        ?>
                    </datalist>
                    <label for="tx_transfer_exchange_id_to">Exchange:</label>
                    <input type="text" name="tx_transfer_exchange_id_to" value="<?php echo $_POST['tx_transfer_exchange_id_to'] ?? ''; ?>" list="tx_tf_to_list">
                    <datalist id="tx_tf_to_list">
                        <?php
                        foreach (Exchange::FullList() as $exchange) {
                            echo '<option>' . $exchange->infos['name'] . '</option>';
                        }
                        ?>
                    </datalist>
                </div>
                <!-- BUY -->
                <div class="tx_type_block <?php echo ($_POST['tx_type'] ?? '') == 'buy' ? 'selected' : ''; ?>" id="buy">
                    <label for="tx_price">Prix:</label>
                    <div class="input_prefix" id="tx_price">
                        <div class="prefix"><i class="fas fa-dollar-sign"></i><span></span></div>
                        <input type="number" name="tx_price" value="<?php echo $_POST['tx_price'] ?? "0"; ?>" min="0" step='0.000000000000000001'>
                    </div>
                    <label for="tx_pair_id">Paire:</label>
                    <input type="text" name="tx_pair_id" value="<?php echo $_POST['tx_pair_id'] ?? ''; ?>" list="tx_pair_list">
                    <datalist id="tx_pair_list">
                        <?php
                        foreach (Pair::FullList() as $pair) {
                            echo '<option>' . $pair->symbol . '</option>';
                        }
                        ?>
                    </datalist>
                    <label for="tx_fee_amount">Montant des frais:</label>
                    <input type="number" name="tx_fee_amount" value="<?php echo $_POST['tx_fee_amount'] ?? "0"; ?>" min="0" step='0.000000000000000001'>
                    <label for="tx_fee_type">Type de frais:</label>
                    <select name="tx_fee_type">
                        <option hidden>--- Choix ---</option>
                        <option value="fixed_currency">[Monnaie]</option>
                        <option value="fixed_index">[Index]</option>
                        <option value="percent_currency">%Monnaie</option>
                        <option value="percent_index">%Index</option>
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
            <?php
        } else {
            ?>
            <h3>Accumulateurs:</h3>
            <table class="table_list">
                <tr>
                    <th>Symbole</th>
                    <th>Nom</th>
                    <th></th>
                </tr>
                <?php
                foreach ($portfolio->accumulators as $accumulator) {
                    echo '<tr>';
                    echo '<td>' . $accumulator->infos['symbol'] . '</td>';
                    echo '<td>' . $accumulator->infos['name'] . '</td>';
                    echo '<td><a href="port_settings.php?port=' . $portfolio->infos['id'] . '&del_acc=' . $accumulator->infos['id'] . '&token=' . $_SESSION['user']['session_token'] . '"><i class="far fa-trash-alt"></i></a></td>';
                    echo '</tr>';
                }
                ?>
            </table>
            <form class="form_new" action="port_settings.php?port=<?php echo $portfolio->infos['id']; ?>" method="post">
                <h3><i class="fab fa-btc fa-1x"></i> Ajouter un accumulateur:</h3>
                <select name="acc_curr_id" required>
                    <option disabled hidden selected>--- Choix ---</option>
                    <?php
                    foreach (Currency::FullList() as $currency) {
                        echo '<option value="' . $currency->infos['id'] . '">[' . $currency->infos['symbol'] . '] ' . $currency->infos['name'] . '</option>';
                    }
                    ?>
                </select>
                <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                <input type="submit" name="new_acc" value="Ajouter">
            </form>
            <?php
        }
        ?>
    </main>
</body>
</html>