<?php
// Start or resume the session
session_start();

// Destroy the session to log the user out
session_destroy();

// Redirect the user to the login page
header("Location: login.php");
exit();
?>
