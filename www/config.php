<?php 
define('DB_HOST', 'db'); 
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', 'AdminFurro'); 
define('DB_NAME', 'FurryFinder_db');

// Connect with the database 
$db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME); 
 
// Display error if failed to connect 
if ($db->connect_errno) { 
    echo "Connection to database is failed: ".$db->connect_error;
    exit();
}