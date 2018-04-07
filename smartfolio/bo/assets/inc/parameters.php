<?php

// SERVER
const ROOT_DIR = 'localhost/1_projets/smartfolio';

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    define('PROTOCOL', 'https');
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    define('PROTOCOL', 'https');
} else {
    define('PROTOCOL', 'http');
}



// DATABASE
const DB_SGBD     = 'mysql';      // DO NOT CHANGE
const DB_DATABASE = 'smartfolio'; // Database name
const DB_HOST     = 'localhost';  // Database host
const DB_USER     = 'root';       // Database user
const DB_PASSWORD = '';           // Database password



// MAILING
const NO_REPLY_ADDR = 'noreply@localhost.loc';



// IN-APP
const OHLC_FETCH_PREVIOUS  = true;   // Get previous unfetched pair data
const OHLC_FETCH_RECURSIVE = true;   // Update recursively if API data allowance != 0
const OHLC_LOGS            = true;   // Save logs
const OHLC_LOGS_DAILY      = true;   // per day else per month

?>