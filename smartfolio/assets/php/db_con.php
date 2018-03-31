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
    $db = new PDO('' . $sgbd . ':host=' . $host . ';dbname=' . $database . ';',
    '' . $user . '', '' . $password . '', $options);
    global $db;
    // $DB->setAttribute(PDO::ATTR_EMULATE_PREPARES,TRUE);
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
session_start();
?>
