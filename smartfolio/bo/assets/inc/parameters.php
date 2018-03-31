<?php

// DOMAIN
const ROOT_DIR = 'localhost/1_projets/smartfolio';

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    define('PROTOCOL', 'https');
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    define('PROTOCOL', 'https');
} else {
    define('PROTOCOL', 'http');
}



// MAILING
const NO_REPLY_ADDR = 'noreply@localhost.loc';

?>