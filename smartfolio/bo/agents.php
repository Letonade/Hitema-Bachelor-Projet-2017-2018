<?php
include 'assets/inc/init.php';

// MANAGERS ONLY
if (!User::AmIManager()) {
    header("Location: index.php");
}

// SELECT AGENT
if (isset($_GET['edit']) || isset($_GET['delete'])) {
    try {
        $agent = new Agent($_GET['edit'] ?? $_GET['delete']);
    } catch (\Exception $e) {
        App::Respond('Agent', $e->getMessage());
    }
}

// ACTIONS
if (isset($_POST['token']) && User::CheckToken($_POST['token'])) {

    // NEW AGENT
    if (isset($_POST['new_agent'])) {
        $new_agent = User::Create(array(
            "user_name"    => $_POST['user_name'],
            "user_email"   => $_POST['user_email'],
            "user_manager" => $_POST['user_manager'],
            "user_type"    => $_POST['user_type']
        ));
        App::Respond(
            'Nouvel agent',
            $new_agent[0] ? null : $new_agent[1]
        );

        // EDIT AGENT
    } elseif (isset($_POST['edit_agent']) && isset($agent)) {
        $edit_agent = $agent->Edit(array(
            "user_manager" => $_POST['user_manager'],
            "user_type"    => $_POST['user_type'],
            "user_id"      => $_POST['user_id']
        ));
        App::Respond(
            'Modifications agent',
            $edit_agent[0] ? null : $edit_agent[1]
        );
    }
} elseif (isset($_GET['token']) && User::CheckToken($_GET['token'])) {

    // DELETE AGENT
    if (isset($_GET['delete']) && isset($agent)) {
        $del_agent = $agent->Delete();
        App::Respond(
            'Suppression de ' . $agent->infos['name'],
            $del_agent[0] ? null : $del_agent[1]
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
    <title>Agents</title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_smartfolio.php';
    ?>
    <main id="main">
        <div id="actions">
            <nav>
                <a href="?add"><i class="fas fa-user-plus"></i> Nouvel agent</a>
                <a href="agents.php"><i class="far fa-users"></i> Mon équipe</a>
                <a href="?all"><i class="fas fa-address-book"></i> Liste complète</a>
            </nav>
            <form action="agents.php" method="get">
                <input type="text" name="search" value="<?php echo $_GET['search'] ?? ''; ?>">
                <input type="submit" value="Rechercher">
            </form>
        </div>
        <?php
        App::DisplayMessages();

        // NEW CUSTOMER
        if (isset($_GET['add'])) {
            ?>
            <form class="form_new" action="agents.php?add" method="post">
                <h3><i class="fas fa-user-plus fa-1x"></i> Nouvel agent:</h3>
                <label for="user_name">Nom:</label>
                <input type="text" name="user_name" value="<?php echo $_POST['user_name'] ?? ''; ?>" placeholder="Nom de l'agent" required>
                <label for="user_email">Email:</label>
                <input type="text" name="user_email" value="<?php echo $_POST['user_email'] ?? ''; ?>" placeholder="Adresse email de l'agent" required>
                <label for="user_manager">Manager:</label>
                <select name="user_manager">
                    <option value="0"></option>
                    <?php
                    foreach (Agent::ManagerList() as $manager) {
                        $save_form = isset($_POST['user_manager']) && $_POST['user_manager'] == $manager->infos['id'];
                        echo '<option value="' . $manager->infos['id'] . '"' . ($save_form ? ' selected' : '') . '>' . $manager->infos['name'] . '</option>';
                    }
                    ?>
                </select>
                <label for="user_type">Statut:</label>
                <select name="user_type">
                    <option value="agent"<?php echo isset($_POST['user_type']) && $_POST['user_type'] == 'agent' ? ' selected' : ''; ?>>Agent</option>
                    <option value="manager"<?php echo isset($_POST['user_type']) && $_POST['user_type'] == 'manager' ? ' selected' : ''; ?>>Manager</option>
                </select>
                <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                <input type="submit" name="new_agent" value="Nouvel agent">
            </form>
            <?php
        } elseif (isset($_GET['edit'])) {
            ?>
            <form class="form_new" action="agents.php?edit=<?php echo $_GET['edit']; ?>" method="post">
                <h3><i class="far fa-edit fa-1x"></i> <?php echo $agent->infos['name'] ?? ''; ?>:</h3>
                <label for="user_manager">Manager:</label>
                <select name="user_manager">
                    <option value="0"></option>
                    <?php
                    foreach (Agent::ManagerList() as $manager) {
                        $save_form = $agent->infos['manager'] == $manager->infos['id'];
                        echo '<option value="' . $manager->infos['id'] . '"' . ($save_form ? ' selected' : '') . '>' . $manager->infos['name'] . '</option>';
                    }
                    ?>
                </select>
                <label for="user_type">Statut:</label>
                <select name="user_type">
                    <option value="agent"<?php echo $agent->infos['type'] == 'agent' ? ' selected' : ''; ?>>Agent</option>
                    <option value="manager"<?php echo $agent->infos['type'] == 'manager' ? ' selected' : ''; ?>>Manager</option>
                </select>
                <input type="hidden" name="user_id" value="<?php echo $agent->infos['id']; ?>">
                <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                <input type="submit" name="edit_agent" value="Modifier">
            </form>
            <?php

            // AGENT LIST
        } else {
            ?>
            <h2><?php echo isset($_GET['search']) ? 'Recherche: ' . $_GET['search'] : (isset($_GET['all']) ? 'Liste complète des agents' : 'Mon équipe'); ?></h2>
            <table class="table_list">
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Manager</th>
                    <th>Portfolios actuels</th>
                    <th></th>
                    <th></th>
                </tr>
                <?php
                foreach ((isset($_GET['search']) ? Agent::Search($_GET['search']) : (isset($_GET['all']) ? Agent::FullList() : Agent::MyTeam())) as $agent) {
                    ?>
                    <tr>
                        <td><?php echo $agent->infos['name']; ?></td>
                        <td><a href="mailto:<?php echo $agent->infos['email']; ?>"><?php echo $agent->infos['email']; ?></a></td>
                        <td><?php echo $agent->infos['type']; ?></td>
                        <td><?php echo isset($agent->infos['manager']) ? (new Agent($agent->infos['manager']))->infos['name'] : ''; ?></td>
                        <td><?php echo 0; ?></td>
                        <td class="act act-pos"><a href="?edit=<?php echo $agent->infos['id']; ?>"><i class="far fa-edit"></i></a></td>
                        <td class="act act-neg"><a href="?<?php echo (isset($_GET['all']) ? 'all&' : '') . 'delete=' . $agent->infos['id'] . '&token=' . $_SESSION['user']['session_token']; ?>"><i class="far fa-trash-alt"></i></a></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }
        ?>
    </main>
</body>
</html>