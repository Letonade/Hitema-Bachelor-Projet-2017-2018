<?php
$sgbd = "mysql";
$host = "localhost";
$database = "smartfolio";
$charset = "utf8";
$user = "root";
$password = "";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
];
try {
    $db = new PDO('' . DB_SGBD . ':host=' . DB_HOST . ';dbname=' . DB_DATABASE . ';',
    '' . DB_USER . '', '' . DB_PASSWORD . '', $options);
    global $db;
    // $DB->setAttribute(PDO::ATTR_EMULATE_PREPARES,TRUE);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
session_start();
?>
