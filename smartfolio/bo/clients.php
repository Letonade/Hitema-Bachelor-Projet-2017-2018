<?php
include 'assets/inc/init.php';

// MANAGERS ONLY
if (!User::AmIManager()) {
    header("Location: index.php");
}

// SELECT CUSTOMER
if (isset($_GET['edit']) || isset($_GET['delete'])) {
    try {
        $customer = new Customer($_GET['edit'] ?? $_GET['delete']);
    } catch (\Exception $e) {
        App::Respond('Client', $e->getMessage());
    }
}

// ACTIONS
if (isset($_POST['token']) && User::CheckToken($_POST['token'])) {

    // NEW CUSTOMER
    if (isset($_POST['new_cust'])) {
        $new_cust = Customer::Create(array(
            "cust_name"     => $_POST['cust_name'],
            "cust_company"  => $_POST['cust_company'],
            "cust_email"    => $_POST['cust_email']
        ));
        App::Respond(
            'Nouveau client',
            $new_cust[0] ? null : $new_cust[1]
        );

        // EDIT CUSTOMER
    } elseif (isset($_POST['edit_cust']) && isset($customer)) {
        $edit_cust = $customer->Edit(array(
            "cust_name"     => $_POST['cust_name'],
            "cust_company"  => $_POST['cust_company'],
            "cust_email"    => $_POST['cust_email']
        ));
        App::Respond(
            'Modifications client',
            $edit_cust[0] ? null : $edit_cust[1]
        );
    }
} elseif (isset($_GET['token']) && User::CheckToken($_GET['token'])) {

    // DELETE CUSTOMER
    if (isset($_GET['delete']) && isset($customer)) {
        $del_cust = $customer->Delete();
        App::Respond(
            'Suppression de ' . $customer->infos['name'],
            $del_cust[0] ? null : $del_cust[1]
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
    <title>Clients</title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_smartfolio.php';
    ?>
    <main id="main">
        <div id="actions">
            <nav>
                <a href="?add"><i class="fas fa-user-plus"></i> Nouveau client</a>
                <a href="clients.php"><i class="far fa-users"></i> Client actuels</a>
                <a href="?all"><i class="fas fa-address-book"></i> Liste complète</a>
            </nav>
            <form action="clients.php" method="get">
                <input type="text" name="search" value="<?php echo $_GET['search'] ?? ''; ?>">
                <input type="submit" value="Rechercher">
            </form>
        </div>
        <?php
        App::DisplayMessages();

        // NEW CUSTOMER
        if (isset($_GET['add'])) {
            ?>
            <form class="form_new" action="clients.php?add" method="post">
                <h3><i class="fas fa-user-plus fa-1x"></i> Nouveau client:</h3>
                <label for="cust_name">Nom:</label>
                <input type="text" name="cust_name" value="<?php echo $_POST['cust_name'] ?? ''; ?>" placeholder="Nom du client" required>
                <label for="cust_company">Société:</label>
                <input type="text" name="cust_company" value="<?php echo $_POST['cust_company'] ?? ''; ?>" placeholder="Société du client">
                <label for="cust_email">Email:</label>
                <input type="text" name="cust_email" value="<?php echo $_POST['cust_email'] ?? ''; ?>" placeholder="Adresse email du client" required>
                <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                <input type="submit" name="new_cust" value="Nouveau client">
            </form>
            <?php
        } elseif (isset($_GET['edit'])) {
            ?>
            <form class="form_new" action="clients.php?edit=<?php echo $_GET['edit']; ?>" method="post">
                <h3><i class="far fa-edit fa-1x"></i> <?php echo $customer->infos['name'] ?? ''; ?>:</h3>
                <label for="cust_name">Nom:</label>
                <input type="text" name="cust_name" value="<?php echo $customer->infos['name'] ?? ''; ?>" placeholder="Nom du client" required>
                <label for="cust_company">Société:</label>
                <input type="text" name="cust_company" value="<?php echo $customer->infos['company'] ?? ''; ?>" placeholder="Société du client">
                <label for="cust_email">Email:</label>
                <input type="text" name="cust_email" value="<?php echo $customer->infos['email'] ?? ''; ?>" placeholder="Adresse email du client" required>
                <input type="hidden" name="cust_id" value="<?php echo $customer->infos['id'] ?? ''; ?>">
                <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                <input type="submit" name="edit_cust" value="Modifier">
            </form>
            <?php

            // CUSTOMER LIST
        } else {
            ?>
            <h2><?php echo isset($_GET['search']) ? 'Recherche: ' . $_GET['search'] : (isset($_GET['all']) ? 'Liste complète des clients' : 'Client actuels'); ?></h2>
            <table class="table_list">
                <tr>
                    <th>Nom</th>
                    <th>Société</th>
                    <th>Email</th>
                    <th>Portfolios</th>
                    <th>Portfolios actifs</th>
                    <th></th>
                    <th></th>
                </tr>
                <?php
                foreach ((isset($_GET['search']) ? Customer::Search($_GET['search']) : (isset($_GET['all']) ? Customer::FullList() : Customer::ActiveList())) as $cust) {
                    ?>
                    <tr>
                        <td><?php echo $cust->infos['name']; ?></td>
                        <td><?php echo $cust->infos['company']; ?></td>
                        <td><a href="mailto:<?php echo $cust->infos['email']; ?>"><?php echo $cust->infos['email']; ?></a></td>
                        <td><?php echo $cust->CountTotalFolios(); ?></td>
                        <td><?php echo $cust->CountActiveFolios(); ?></td>
                        <td class="act act-pos"><a href="?edit=<?php echo $cust->infos['id']; ?>"><i class="far fa-edit"></i></a></td>
                        <td class="act act-neg"><a href="?<?php echo (isset($_GET['all']) ? 'all&' : '') . 'delete=' . $cust->infos['id'] . '&token=' . $_SESSION['user']['session_token']; ?>"><i class="far fa-trash-alt"></i></a></td>
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