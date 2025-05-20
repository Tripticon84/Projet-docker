<?php

session_start();
session_unset();
session_destroy();

// Suprimer le cookie token
if (isset($_COOKIE['admin_token'])) {
    setcookie('admin_token', '', time() - 3600, '/');
}

header("Location: /backOffice/index.php");
exit();
