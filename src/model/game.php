<?php
function words7_path() {
  return file_exists('data/words7.txt')
         ? 'data/words7.txt'
         : '/var/www/html/homework/words7.txt';
}

function wordbank_path() {
  return file_exists('data/word_bank.json')
         ? 'data/word_bank.json'
         : '/var/www/html/homework/word_bank.json';
}

function words7() {
  $path = words7_path();
  $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if ($lines === false) return [];
  $out = [];
  foreach ($lines as $w) {
    $w = strtolower(trim($w));
    if (preg_match('/^[a-z]{7}$/', $w)) {
      $out[] = $w;
    }
  }
  return $out;
}

function dict_by_len() {
  static $cached = null;
  if ($cached !== null) return $cached;

  $path = wordbank_path();
  $json = @file_get_contents($path);
  if ($json === false) return $cached = [];

  $data = json_decode($json, true);
  if (!is_array($data)) return $cached = [];

  $buckets = [];

  $isAssoc = array_keys($data) !== range(0, count($data) - 1);

  if ($isAssoc) {
    foreach ($data as $lenKey => $list) {
      if (!is_array($list)) continue;
      $len = (int)$lenKey;
      if ($len <= 0) continue;

      foreach ($list as $item) {
        if (!is_string($item)) continue;
        $w = strtolower(trim($item));
        if ($w === '') continue;
        $L = strlen($w);
        $bucketLen = ($L > 0 ? $L : $len);
        if (!isset($buckets[$bucketLen])) $buckets[$bucketLen] = [];
        $buckets[$bucketLen][$w] = true;
      }
    }
  } else {
    foreach ($data as $item) {
      if (!is_string($item)) continue;
      $w = strtolower(trim($item));
      if ($w === '') continue;
      $L = strlen($w);
      if ($L <= 0) continue;
      if (!isset($buckets[$L])) $buckets[$L] = [];
      $buckets[$L][$w] = true;
    }
  }

  return $cached = $buckets;
}

function game_start() {
  $pool = words7();
  $target = !empty($pool) ? $pool[array_rand($pool)] : 'anagram';
  $_SESSION['game'] = [
    'targetWord'   => $target,
    'letters'      => str_shuffle($target),
    'score'        => 0,
    'validGuesses' => [],
    'invalidCount' => 0
  ];
}

function game_reshuffle() {
  $_SESSION['game']['letters'] = str_shuffle($_SESSION['game']['targetWord']);
}

function multiset_ok($guess, $target) {
  $c1 = array_count_values(str_split($guess));
  $c2 = array_count_values(str_split($target));
  foreach ($c1 as $ch => $cnt) {
    if (!isset($c2[$ch]) || $cnt > $c2[$ch]) return false;
  }
  return true;
}

function points_for_len($n) {
  $map = [1=>1, 2=>2, 3=>4, 4=>8, 5=>15, 6=>30];
  return isset($map[$n]) ? $map[$n] : 0;
}

function game_apply_guess($guess) {
  $guess = strtolower(trim($guess));
  if ($guess === '') {
    return ['ok'=>false,'msg'=>'Enter a word','won'=>false];
  }

  $target = $_SESSION['game']['targetWord'];

  if (!preg_match('/^[a-z]+$/', $guess) || !multiset_ok($guess, $target)) {
    $_SESSION['game']['invalidCount']++;
    return ['ok'=>false,'msg'=>'Used disallowed letters.','won'=>false];
  }

  $len = strlen($guess);

  if ($len === 7 && $guess === $target) {
    $_SESSION['game']['validGuesses'][] = $guess;

    return ['ok'=>true,'msg'=>'You found the 7-letter word!','won'=>true];
  }

  static $dict = null;
  if ($dict === null) $dict = dict_by_len();
  if (!isset($dict[$len][$guess])) {
    $_SESSION['game']['invalidCount']++;
    return ['ok'=>false,'msg'=>'Not a valid dictionary word.','won'=>false];
  }

  if (in_array($guess, $_SESSION['game']['validGuesses'], true)) {
    return ['ok'=>false,'msg'=>'Already guessed that.','won'=>false];
  }

  $_SESSION['game']['validGuesses'][] = $guess;
  $_SESSION['game']['score'] += points_for_len($len);
  return ['ok'=>true,'msg'=>'Nice! +' . points_for_len($len) . ' points.','won'=>false];
}

