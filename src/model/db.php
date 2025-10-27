<?php
function db_connect() {
  $isLocal = isset($_SERVER['SERVER_NAME']) && 
             ($_SERVER['SERVER_NAME'] === 'localhost' || 
             $_SERVER['SERVER_NAME'] === '127.0.0.1');

  if ($isLocal) {
    $host = 'localhost';
    $db   = 'hw3_local';
    $user = 'jonathanorellana';
    $pass = ''; 
  } else {
    $host = 'localhost';
    $db   = 'YOUR_COMPUTING_ID';   
    $user = 'YOUR_COMPUTING_ID';   
    $pass = 'PASSWORD_FROM_CANVAS'; 
  }

  $conn = pg_connect("host=$host dbname=$db user=$user password=$pass");

  if (!$conn) {
    die('DB connection failed');
  }

  return $conn;
}
?>
