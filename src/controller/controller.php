<?php
require_once __DIR__ . '/../model/db.php';
require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/game.php';

function render($template, $data = []) {
  extract($data);
  $content = "src/view/$template.php";
  include 'src/view/layout.php';
}

function action_welcome() {
  render('welcome');
}

function action_login() {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return render('welcome');

  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  if ($name === '' || $email === '' || $pass === '') {
    $error = "Please fill all fields.";
    return render('welcome', compact('error'));
  }

  $db = db_connect();
  $user = user_find_by_email($db, $email);
  if ($user) {
    if (!password_verify($pass, $user['pass_hash'])) {
      $error = "Incorrect password.";
      return render('welcome', compact('error'));
    }
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
  } else {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $id = user_create($db, $name, $email, $hash);
    $_SESSION['user_id'] = $id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
  }

  game_start();
  header('Location: ?command=play'); exit;
}

function guard() {
  if (!isset($_SESSION['user_id'])) { header('Location: ?command=welcome'); exit; }
}

function action_play() {
  guard();
  $state = $_SESSION['game'];
  render('game', $state);
}

function action_guess() {
  guard();
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return header('Location: ?command=play');

  $guess = trim(strtolower($_POST['guess'] ?? ''));
  $result = game_apply_guess($guess);
  if ($result['won']) { header('Location: ?command=gameover'); exit; }
  $state = $_SESSION['game']; $state['flash'] = $result['msg'];
  render('game', $state);
}

function action_reshuffle() {
  guard();
  game_reshuffle();
  header('Location: ?command=play'); exit;
}

function action_quit() {
  session_destroy();
  header('Location: ?command=welcome'); exit;
}

function action_logout() { action_quit(); }

function action_gameover() {
  guard();
  $state = $_SESSION['game'];
  render('gameover', $state);
}
