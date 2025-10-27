<?php
session_start();
require 'src/controller/controller.php';

$cmd = $_GET['command'] ?? 'welcome';
$allowed = ['welcome','login','play','guess','reshuffle','gameover','quit','logout'];
if (!in_array($cmd, $allowed)) $cmd = 'welcome';

$fn = "action_$cmd";
$fn();
