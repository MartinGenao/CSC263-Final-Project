<?php
session_start();

// Destroy the session and redirect to the login page
session_unset();
session_destroy();

header('Location: psirt_homepage.php');
exit();
?>