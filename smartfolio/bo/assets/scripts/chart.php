<?php
include '../inc/parameters.php';
include '../../../assets/php/db_con.php';
include '../../../assets/php/app.class.php';

App::SetDB($db);

include '../php/currency.class.php';
include '../php/exchange.class.php';
include '../php/pair.class.php';

$pair = new Pair($_GET['pair']);

header("Content-type: text/csv");

echo 'Date,Open,High,Low,Close,Volume' . "\n";

foreach ($pair->GetChartData() as $candlestick) {
    echo $candlestick['ohlc_timestamp'] . ',';
    echo $candlestick['ohlc_open'] . ',';
    echo $candlestick['ohlc_high'] . ',';
    echo $candlestick['ohlc_low'] . ',';
    echo $candlestick['ohlc_close'] . ',';
    echo $candlestick['ohlc_volume'] . "\n";
}

?>