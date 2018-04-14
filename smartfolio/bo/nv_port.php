<?php
include 'assets/inc/init.php';

// MANAGERS ONLY
if (!User::AmIManager()) {
    header("Location: index.php");
}

// ACTIONS
if (isset($_POST['token']) && User::CheckToken($_POST['token'])) {

    // ADD NEW PORTFOLIO
    if (isset($_POST['new_portfolio'])) {
        $new_portfolio = Portfolio::Create(array(
            "port_cust_id" => $_POST['port_cust_id'],
            "port_agent_id" => $_POST['port_agent_id']
        ));
        App::Respond(
            'Nouveau portefeuille',
            $new_portfolio[0] ? null : $new_portfolio[1]
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
    <title>Nouveau Portefeuille</title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_smartfolio.php';
    ?>
    <main id="main">
        <?php App::DisplayMessages(); ?>
        <form class="form_new" action="nv_port.php" method="post">
            <h3>Créer un nouveau portefeuille:</h3>
            <label for="port_cust_id">Client</label>
            <select name="port_cust_id" required>
                <option disabled hidden selected>--- Choix ---</option>
                <?php
                foreach (Customer::FullList() as $customer) {
                    $selected = isset($_POST['port_cust_id']) && $_POST['port_cust_id'] == $customer->infos['id'] ? ' selected' : '';
                    echo '<option value="' . $customer->infos['id'] . '"' . $selected . '>' . $customer->infos['name'] . '</option>';
                }
                ?>
            </select>
            <label for="port_agent_id">Agent</label>
            <select name="port_agent_id" required>
                <option disabled hidden selected>--- Choix ---</option>
                <?php
                $selected = isset($_POST['port_agent_id']) && $_POST['port_agent_id'] == $_SESSION['user']['id'] ? ' selected' : '';
                echo '<option value="' . $_SESSION['user']['id'] . '"' . $selected . '>'. $_SESSION['user']['name'] . '</option>';
                foreach (Agent::MyTeam() as $agent) {
                    $selected = isset($_POST['port_agent_id']) && $_POST['port_agent_id'] == $agent->infos['id'] ? ' selected' : '';
                    echo '<option value="' . $agent->infos['id'] . '"' . $selected . '>' . $agent->infos['name'] . '</option>';
                }
                ?>
            </select>
            <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
            <input type="submit" name="new_portfolio" value="Nouveau portefeuille">
        </form>
    </main>
</body>
</html>