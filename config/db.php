<?php
/*
|------------------------------------------
| DATABASE CONNECTION FILE
|------------------------------------------
| This file connects PHP to MySQL database
| We use mysqli (MySQL Improved) extension
| Include this file in every page that
| needs database access
|------------------------------------------
*/

// Database credentials
define('DB_HOST', 'localhost');  // XAMPP default
define('DB_USER', 'root');       // XAMPP default username
define('DB_PASS', '');           // XAMPP default (empty)
define('DB_NAME', 'internlink');  // your database name

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
// If connection fails, stop everything and show error
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to utf8
// This supports all characters including special ones
mysqli_set_charset($conn, "utf8");

/*
Learn:
mysqli_connect() returns a connection object
We store it in $conn
$conn is used in every database query
If it returns false, die() stops the script
*/
?>