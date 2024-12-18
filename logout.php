<?php
session_start();

session_unset();
session_destroy();

header('Location: psirt_homepage.php');
exit();
?>