<?php
include 'assets/inc/init.php';

// MANAGERS ONLY
if (!User::AmIManager()) {
    header("Location: index.php");
}

// SELECT ITEM
if (isset($_GET['edit']) || isset($_GET['delete'])) {
    // CURRENCY
    if (isset($_GET['currency'])) {
        try {
            $currency = new Currency($_GET['edit'] ?? $_GET['delete']);
        } catch (\Exception $e) {
            App::Respond('Monnaie', $e->getMessage());
        }

        // EXCHANGE
    } elseif (isset($_GET['exchange'])) {
        try {
            $exchange = new Exchange($_GET['edit'] ?? $_GET['delete']);
        } catch (\Exception $e) {
            App::Respond('Échange', $e->getMessage());
        }

        // PAIR
    } else {
        try {
            $pair = new Pair($_GET['edit'] ?? $_GET['delete']);
        } catch (\Exception $e) {
            App::Respond('Paire', $e->getMessage());
        }
    }
}

// ACTIONS
if (isset($_POST['token']) && User::CheckToken($_POST['token'])) {

    // CURRENCY
    if (isset($_GET['currency'])) {

        // NEW CURRENCY
        if (isset($_POST['new_currency'])) {
            $new_currency = Currency::Create(array(
                "curr_name"   => $_POST['curr_name'],
                "curr_symbol" => $_POST['curr_symbol']
            ));
            App::Respond(
                'Nouvelle monnaie',
                $new_currency[0] ? null : $new_currency[1]
            );

            // EDIT CURRENCY
        } elseif (isset($_POST['edit_currency']) && isset($currency)) {
            $edit_currency = $currency->Edit(array(
                "curr_name"   => $_POST['curr_name'],
                "curr_symbol" => $_POST['curr_symbol']
            ));
            App::Respond(
                'Modifications monnaie',
                $edit_currency[0] ? null : $edit_currency[1]
            );

            // DELETE CURRENCY
        } elseif (isset($_POST['delete_currency']) && isset($currency)) {
            $del_currency = $currency->Delete(isset($_POST['confirm_supp_currency']));
            App::Respond(
                'Suppression de ' . $currency->infos['name'],
                $del_currency[0] ? null : $del_currency[1]
            );
        }

        // EXCHANGE
    } elseif (isset($_GET['exchange'])) {

        // NEW EXCHANGE
        if (isset($_POST['new_exchange'])) {
            $new_exchange = Exchange::Create(array(
                "exchange_name" => $_POST['exchange_name']
            ));
            App::Respond(
                'Nouvel échange',
                $new_exchange[0] ? null : $new_exchange[1]
            );

            // EDIT EXCHANGE
        } elseif (isset($_POST['edit_exchange']) && isset($exchange)) {
            $edit_exchange = $exchange->Edit(array(
                "exchange_name" => $_POST['exchange_name']
            ));
            App::Respond(
                'Modifications échange',
                $edit_exchange[0] ? null : $edit_exchange[1]
            );

            // DELETE EXCHANGE
        } elseif (isset($_POST['delete_exchange']) && isset($exchange)) {
            $del_exchange = $exchange->Delete(isset($_POST['confirm_supp_exchange']));
            App::Respond(
                'Suppression de ' . $exchange->infos['name'],
                $del_exchange[0] ? null : $del_exchange[1]
            );
        }

        // PAIR
    } else {

        // NEW PAIR
        if (isset($_POST['new_pair'])) {
            $new_pair = Pair::Create(array(
                "pair_curr_a"      => $_POST['pair_curr_a'],
                "pair_curr_b"      => $_POST['pair_curr_b'],
                "pair_exchange_id" => $_POST['pair_exchange_id'],
                "pair_api_url"     => $_POST['pair_api_url']
            ));
            App::Respond(
                'Nouvelle paire',
                $new_pair[0] ? null : $new_pair[1]
            );

            // EDIT PAIR
        } elseif (isset($_POST['edit_pair']) && isset($pair)) {
            $edit_pair = $pair->Edit(array(
                "pair_curr_a"      => $_POST['pair_curr_a'],
                "pair_curr_b"      => $_POST['pair_curr_b'],
                "pair_exchange_id" => $_POST['pair_exchange_id'],
                "pair_api_url"     => $_POST['pair_api_url']
            ));
            App::Respond(
                'Modifications paire',
                $edit_pair[0] ? null : $edit_pair[1]
            );

            // DELETE PAIR
        } elseif (isset($_POST['delete_pair']) && isset($pair)) {
            $del_pair = $pair->Delete(isset($_POST['confirm_supp_pair']));
            App::Respond(
                'Suppression de ' . $pair->symbol,
                $del_pair[0] ? null : $del_pair[1]
            );
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
    <title>Paramètres</title>
</head>
<body>
    <?php
    // HEADER
    include 'assets/inc/header_smartfolio.php';
    ?>
    <main id="main">
        <div id="actions">
            <nav>
                <a <?php App::SetLink('setup.php?currency'); ?>><i class="fab fa-btc"></i> Monnaies</a>
                <a <?php App::SetLink('setup.php?pair'); ?>><i class="fas fa-retweet"></i> Paires</a>
                <a <?php App::SetLink('setup.php?exchange'); ?>><i class="fas fa-cart-arrow-down"></i> Échanges</a>
            </nav>
            <?php
            if (isset($_GET['currency'])) {
                ?>
                <nav>
                    <a href="?currency&add"><i class="fas fa-plus"></i> Ajouter</a>
                    <a href="?currency"><i class="fas fa-list"></i> Liste</a>
                </nav>
                <?php
            } elseif (isset($_GET['exchange'])) {
                ?>
                <nav>
                    <a href="?exchange&add"><i class="fas fa-plus"></i> Ajouter</a>
                    <a href="?exchange"><i class="fas fa-list"></i> Liste</a>
                </nav>
                <?php
            } else {
                ?>
                <nav>
                    <a href="?pair&add"><i class="fas fa-plus"></i> Ajouter</a>
                    <a href="?pair"><i class="fas fa-list"></i> Liste</a>
                </nav>
                <?php
            }
            ?>
            <form action="setup.php" method="get">
                <input type="hidden" name="<?php echo isset($_GET['currency']) ? 'currency' : (isset($_GET['exchange']) ? 'exchange' : 'pair'); ?>">
                <input type="text" name="search" value="<?php echo $_GET['search'] ?? ''; ?>">
                <input type="submit" value="Rechercher">
            </form>
        </div>
        <?php
        App::DisplayMessages();

        // CURRENCY
        if (isset($_GET['currency'])) {
            // ADD CURRENCY
            if (isset($_GET['add'])) {
                ?>
                <form class="form_new" action="setup.php?currency&add" method="post">
                    <h3><i class="fab fa-btc fa-1x"></i> Nouvelle monnaie:</h3>
                    <label for="curr_name">Nom:</label>
                    <input type="text" name="curr_name" value="<?php echo $_POST['curr_name'] ?? ''; ?>" placeholder="Nom de la monnaie" required>
                    <label for="curr_symbol">Symbole:</label>
                    <input type="text" name="curr_symbol" value="<?php echo $_POST['curr_symbol'] ?? ''; ?>" placeholder="Symbole de la monnaie" required>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                    <input type="submit" name="new_currency" value="Nouvelle monnaie">
                </form>
                <?php

                // EDIT CURRENCY
            } elseif (isset($_GET['edit'])) {
                ?>
                <form class="form_new" action="setup.php?currency&edit=<?php echo $_GET['edit']; ?>" method="post">
                    <h3><i class="far fa-edit fa-1x"></i> <?php echo $currency->infos['name'] ?? ''; ?>:</h3>
                    <label for="curr_name">Nom:</label>
                    <input type="text" name="curr_name" value="<?php echo $currency->infos['name'] ?? ''; ?>" placeholder="Nom de la monnaie" required>
                    <label for="curr_symbol">Symbole:</label>
                    <input type="text" name="curr_symbol" value="<?php echo $currency->infos['symbol'] ?? ''; ?>" placeholder="Symbole de la monnaie" required>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                    <input type="submit" name="edit_currency" value="Modifier">
                    <!-- DELETE -->
                    <h3><br><i class="far fa-trash-alt"></i> Supprimer</h3>
                    <p>La suppression de la monnaie entraînera la suppression des paires liées et des données historique associées.</p>
                    <label for="confirm_supp_currency">Confirmez pour supprimer: <input type="checkbox" name="confirm_supp_currency"></label>
                    <input type="submit" name="delete_currency" value="Supprimer">
                </form>
                <?php

                // CURRENCY LIST
            } else {
                ?>
                <h2><?php echo isset($_GET['search']) ? 'Monnaies - Recherche: ' . $_GET['search'] : 'Liste des monnaies'; ?></h2>
                <table class="table_list">
                    <tr>
                        <th>Nom</th>
                        <th>Symbole</th>
                        <th></th>
                    </tr>
                    <?php
                    foreach ((isset($_GET['search']) ? Currency::Search($_GET['search']) : Currency::FullList()) as $currency) {
                        ?>
                        <tr>
                            <td><?php echo $currency->infos['name']; ?></td>
                            <td><?php echo $currency->infos['symbol']; ?></td>
                            <td class="act act-pos"><a href="?currency&edit=<?php echo $currency->infos['id']; ?>"><i class="far fa-edit"></i></a></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <?php
            }

            // EXCHANGE
        } elseif (isset($_GET['exchange'])) {
            // ADD EXCHANGE
            if (isset($_GET['add'])) {
                ?>
                <form class="form_new" action="setup.php?exchange&add" method="post">
                    <h3><i class="fas fa-cart-arrow-down fa-1x"></i> Nouvel échange:</h3>
                    <label for="exchange_name">Nom:</label>
                    <input type="text" name="exchange_name" value="<?php echo $_POST['exchange_name'] ?? ''; ?>" placeholder="Nom de l'échange" required>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                    <input type="submit" name="new_exchange" value="Nouvel échange">
                </form>
                <?php

                // EDIT EXCHANGE
            } elseif (isset($_GET['edit'])) {
                ?>
                <form class="form_new" action="setup.php?exchange&edit=<?php echo $_GET['edit']; ?>" method="post">
                    <h3><i class="far fa-edit fa-1x"></i> <?php echo $exchange->infos['name'] ?? ''; ?>:</h3>
                    <label for="exchange_name">Nom:</label>
                    <input type="text" name="exchange_name" value="<?php echo $exchange->infos['name'] ?? ''; ?>" placeholder="Nom de l'échange" required>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                    <input type="submit" name="edit_exchange" value="Modifier">
                    <!-- DELETE -->
                    <h3><br><i class="far fa-trash-alt"></i> Supprimer</h3>
                    <p>La suppression de l'échange entraînera la suppression des paires liées et des données historique associées.</p>
                    <label for="confirm_supp_exchange">Confirmez pour supprimer: <input type="checkbox" name="confirm_supp_exchange"></label>
                    <input type="submit" name="delete_exchange" value="Supprimer">
                </form>
                <?php

                // EXCHANGE LIST
            } else {
                ?>
                <h2><?php echo isset($_GET['search']) ? 'Échanges - Recherche: ' . $_GET['search'] : 'Liste des échanges'; ?></h2>
                <table class="table_list">
                    <tr>
                        <th>Nom</th>
                        <th></th>
                    </tr>
                    <?php
                    foreach ((isset($_GET['search']) ? Exchange::Search($_GET['search']) : Exchange::FullList()) as $exchange) {
                        ?>
                        <tr>
                            <td><?php echo $exchange->infos['name']; ?></td>
                            <td class="act act-pos"><a href="?exchange&edit=<?php echo $exchange->infos['id']; ?>"><i class="far fa-edit"></i></a></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <?php
            }

            // PAIR
        } else {
            // ADD PAIR
            if (isset($_GET['add'])) {
                ?>
                <form class="form_new" action="setup.php?pair&add" method="post">
                    <h3><i class="fas fa-retweet fa-1x"></i> Nouvelle paire:</h3>
                    <label for="pair_curr_a">Monnaie:</label>
                    <select name="pair_curr_a">
                        <?php
                        foreach (Currency::FullList() as $currency) {
                            $selected = isset($_POST['pair_curr_a']) && $_POST['pair_curr_a'] == $currency->infos['id'] ? ' selected' : '';
                            echo '<option value="' . $currency->infos['id'] . '"' . $selected . '>[' . $currency->infos['symbol'] . '] ' . $currency->infos['name'] . '</option>';
                        }
                        ?>
                    </select>
                    <label for="pair_curr_b">Indexé sur:</label>
                    <select name="pair_curr_b">
                        <?php
                        foreach (Currency::FullList() as $currency) {
                            $selected = isset($_POST['pair_curr_b']) && $_POST['pair_curr_b'] == $currency->infos['id'] ? ' selected' : '';
                            echo '<option value="' . $currency->infos['id'] . '"' . $selected . '>[' . $currency->infos['symbol'] . '] ' . $currency->infos['name'] . '</option>';
                        }
                        ?>
                    </select>
                    <label for="pair_exchange_id">Échange:</label>
                    <select name="pair_exchange_id">
                        <?php
                        foreach (Exchange::FullList() as $exchange) {
                            $selected = isset($_POST['pair_exchange_id']) && $_POST['pair_exchange_id'] == $exchange->infos['id'] ? ' selected' : '';
                            echo '<option value="' . $exchange->infos['id'] . '"' . $selected . '>' . $exchange->infos['name'] . '</option>';
                        }
                        ?>
                    </select>
                    <label for="pair_api_url">URL de l'API:</label>
                    <input type="text" name="pair_api_url" value="<?php echo $_POST['pair_api_url'] ?? ''; ?>" placeholder="https://api.cryptowat.ch/" required>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                    <input type="submit" name="new_pair" value="Nouvelle paire">
                </form>
                <?php

                // EDIT PAIR
            } elseif (isset($_GET['edit'])) {
                ?>
                <form class="form_new" action="setup.php?pair&edit=<?php echo $_GET['edit']; ?>" method="post">
                    <h3><i class="far fa-edit fa-1x"></i> <?php echo $pair->symbol ?? ''; ?>:</h3>
                    <label for="pair_curr_a">Monnaie:</label>
                    <select name="pair_curr_a">
                        <?php
                        foreach (Currency::FullList() as $currency) {
                            $selected = $pair->infos['curr_a'] == $currency->infos['id'] ? ' selected' : '';
                            echo '<option value="' . $currency->infos['id'] . '"' . $selected . '>[' . $currency->infos['symbol'] . '] ' . $currency->infos['name'] . '</option>';
                        }
                        ?>
                    </select>
                    <label for="pair_curr_b">Indexé sur:</label>
                    <select name="pair_curr_b">
                        <?php
                        foreach (Currency::FullList() as $currency) {
                            $selected = $pair->infos['curr_b'] == $currency->infos['id'] ? ' selected' : '';
                            echo '<option value="' . $currency->infos['id'] . '"' . $selected . '>[' . $currency->infos['symbol'] . '] ' . $currency->infos['name'] . '</option>';
                        }
                        ?>
                    </select>
                    <label for="pair_exchange_id">Échange:</label>
                    <select name="pair_exchange_id">
                        <?php
                        foreach (Exchange::FullList() as $exchange) {
                            $selected = $pair->infos['exchange'] == $exchange->infos['id'] ? ' selected' : '';
                            echo '<option value="' . $exchange->infos['id'] . '"' . $selected . '>' . $exchange->infos['name'] . '</option>';
                        }
                        ?>
                    </select>
                    <label for="pair_api_url">URL de l'API:</label>
                    <input type="text" name="pair_api_url" value="<?php echo $pair->infos['api_url']; ?>" placeholder="https://api.cryptowat.ch/" required>
                    <input type="hidden" name="token" value="<?php echo $_SESSION['user']['session_token']; ?>">
                    <input type="submit" name="edit_pair" value="Modifier">
                    <!-- DELETE -->
                    <h3><br><i class="far fa-trash-alt"></i> Supprimer</h3>
                    <p>La suppression de la paire entraînera la suppression des données historique associées.</p>
                    <label for="confirm_supp_pair">Confirmez pour supprimer: <input type="checkbox" name="confirm_supp_pair"></label>
                    <input type="submit" name="delete_pair" value="Supprimer">
                </form>
                <?php

                // PAIR LIST
            } else {
                ?>
                <h2><?php echo isset($_GET['search']) ? 'Paires - Recherche: ' . $_GET['search'] : 'Liste des paires'; ?></h2>
                <table class="table_list">
                    <tr>
                        <th>Symbole</th>
                        <th>Monnaie</th>
                        <th>Index</th>
                        <th>Échange</th>
                        <th>Données</th>
                        <th>URL de l'API</th>
                        <th></th>
                    </tr>
                    <?php
                    foreach ((isset($_GET['search']) ? Pair::Search($_GET['search']) : Pair::FullList()) as $pair) {
                        ?>
                        <tr>
                            <td><?php echo $pair->symbol; ?></td>
                            <td><?php echo $pair->currency->infos['name']; ?></td>
                            <td><?php echo $pair->index->infos['name']; ?></td>
                            <td><?php echo $pair->exchange->infos['name']; ?></td>
                            <td><?php
                            if ($pair->last_update == false) {
                                echo 'Aucune';
                            } else {
                                echo (new DateTime())->setTimeStamp($pair->first_update)->format('d/m/Y H:i') . ' - ';
                                echo (new DateTime())->setTimeStamp($pair->last_update)->format('d/m/Y H:i');
                            }
                            ?></td>
                            <td><a href="<?php echo $pair->infos['api_url']; ?>" target="_blank"><?php echo $pair->infos['api_url']; ?></a></td>
                            <td class="act act-pos"><a href="?pair&edit=<?php echo $pair->infos['id']; ?>"><i class="far fa-edit"></i></a></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <?php
            }
        }
        ?>
    </main>
</body>
</html>