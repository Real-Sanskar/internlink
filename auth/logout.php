<?php
require_once '../includes/functions.php';

/*
Learn: Proper logout steps:
1. Start session (to access it)
2. Unset all session variables
3. Destroy the session
4. Redirect to login
Never just redirect without destroying session!
*/

session_start();
$_SESSION = [];          // Clear all session data
session_destroy();       // Destroy session file

// Redirect to login
header("Location: ../auth/login.php");
exit();
?>