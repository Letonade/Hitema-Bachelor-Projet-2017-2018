<?php

// DEBUG : DEV MODE FUNCTION
function debug($deb){
	$out = $deb;
	if (is_array($out)) {
		$out = implode(',', $out);}
	echo "<script>console.log('debugPHP :".$out."');</script>";
}
// PARAMS & DB
include 'parameters.php';
include '../assets/php/db_con.php';

// REDIRECT IF NOT LOGGED IN
if (!isset($_SESSION['user'])) {
    header("Location: ../#footer");
}

// TRAITS
include 'assets/php/html_transaction.trait.php';
include 'assets/php/html_investment.trait.php';
include 'assets/php/html_alert.trait.php';

// OOP
include '../assets/php/app.class.php';
include '../assets/php/user.class.php';
include 'assets/php/agent.class.php';
include 'assets/php/currency.class.php';
include 'assets/php/customer.class.php';
include 'assets/php/exchange.class.php';
include 'assets/php/investment.class.php';
include 'assets/php/pair.class.php';
include 'assets/php/portfolio.class.php';
include 'assets/php/ohlc_api.class.php';
include 'assets/php/transaction.class.php';
include 'assets/php/alerts.class.php';

App::SetDB($db);

?>