<?php
// Start the session
session_start();

// Unset only the admin-specific session variables
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Redirect to login page
header("Location: index.php");
exit();
?>