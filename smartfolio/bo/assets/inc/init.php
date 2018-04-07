<?php

// PARAMS & DB
include 'parameters.php';
include '../assets/php/db_con.php';

// REDIRECT IF NOT LOGGED IN
if (!isset($_SESSION['user'])) {
    header("Location: ../#footer");
}

// OOP
include '../assets/php/app.class.php';
include '../assets/php/user.class.php';
include 'assets/php/portfolio.class.php';
include 'assets/php/agent.class.php';
include 'assets/php/customer.class.php';
include 'assets/php/pair.class.php';
include 'assets/php/ohlc_api.class.php';
include 'assets/php/currency.class.php';
include 'assets/php/exchange.class.php';

App::SetDB($db);

?>