<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function db_connect() {
  $env = parse_ini_file(__DIR__ . '/../../.env');

  $isLocal = isset($_SERVER['SERVER_NAME']) &&
             ($_SERVER['SERVER_NAME'] === 'localhost' ||
              $_SERVER['SERVER_NAME'] === '127.0.0.1');

  if ($isLocal) {
    $host = $env['LOCAL_HOST'];
    $db   = $env['LOCAL_DB'];
    $user = $env['LOCAL_USER'];
    $pass = $env['LOCAL_PASS'];
  } else {
    $host = $env['SERVER_HOST'];
    $db   = $env['SERVER_DB'];
    $user = $env['SERVER_USER'];
    $pass = $env['SERVER_PASS']; 
  }

  $conn = pg_connect("host=$host port=5432 dbname=$db user=$user password='$pass' connect_timeout=5");

  if (!$conn) {
    echo '<pre>';
    echo 'DB connection failed: ' . pg_last_error();
    echo '</pre>';
    exit;
  }

  return $conn;
}
?>
