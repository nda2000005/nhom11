<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/mnopl/public/');
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$domain = $protocol . "://" . $_SERVER['HTTP_HOST'];

if (!defined('FULL_URL')) {
    define('FULL_URL', $domain . BASE_URL);
}

date_default_timezone_set('Asia/Ho_Chi_Minh');
?>