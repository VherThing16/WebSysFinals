<?php
require_once 'includes/config.php';
require_once 'includes/auth_functions.php';

logoutUser();
header("Location: login.php");
exit();
?>