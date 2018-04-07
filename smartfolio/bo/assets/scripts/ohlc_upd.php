<?php
include '../inc/parameters.php';
include '../../../assets/php/db_con.php';
include '../../../assets/php/app.class.php';

App::SetDB($db);

include '../php/pair.class.php';
include '../php/ohlc_api.class.php';

OHLC_API::DefineLogsUrl();
OHLC_API::APIUpdateOHLC();

?>