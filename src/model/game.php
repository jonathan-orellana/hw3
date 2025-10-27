<?php
// ===== Simple helpers to choose local vs server paths (no fancy logic) =====
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

// ===== Load 7-letter target words (lowercase, exactly 7 letters) =====
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

// ===== Build dictionary buckets by length (very simple) =====
// Expects word_bank.json to be a flat array of strings.
// If it encounters non-strings, it just skips them (keeps it safe & simple).
function dict_by_len() {
  static $cached = null;
  if ($cached !== null) return $cached;

  $path = wordbank_path();
  $json = @file_get_contents($path);
  if ($json === false) return $cached = [];

  $data = json_decode($json, true);
  if (!is_array($data)) return $cached = [];

  $buckets = [];

  // Detect if it's keyed-by-length (associative) or flat array
  $isAssoc = array_keys($data) !== range(0, count($data) - 1);

  if ($isAssoc) {
    // {"1":[...], "2":[...], "3":[...], ...}
    foreach ($data as $lenKey => $list) {
      if (!is_array($list)) continue;
      $len = (int)$lenKey;
      if ($len <= 0) continue;

      foreach ($list as $item) {
        if (!is_string($item)) continue;
        $w = strtolower(trim($item));
        if ($w === '') continue;
        // Prefer the declared length, but fix mismatches
        $L = strlen($w);
        $bucketLen = ($L > 0 ? $L : $len);
        if (!isset($buckets[$bucketLen])) $buckets[$bucketLen] = [];
        $buckets[$bucketLen][$w] = true;
      }
    }
  } else {
    // ["a","an","you", ...]  (flat array)
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


// ===== Game lifecycle (stored in $_SESSION) =====
function game_start() {
  $pool = words7();
  $target = !empty($pool) ? $pool[array_rand($pool)] : 'anagram'; // fallback if file missing
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

// ===== Small utilities =====
function multiset_ok($guess, $target) {
  $c1 = array_count_values(str_split($guess));
  $c2 = array_count_values(str_split($target));
  foreach ($c1 as $ch => $cnt) {
    if (!isset($c2[$ch]) || $cnt > $c2[$ch]) return false;
  }
  return true;
}

function points_for_len($n) {
  // per assignment
  $map = [1=>1, 2=>2, 3=>4, 4=>8, 5=>15, 6=>30];
  return isset($map[$n]) ? $map[$n] : 0;
}

// ===== Apply a guess (simple, straight from spec) =====
function game_apply_guess($guess) {
  $guess = strtolower(trim($guess));
  if ($guess === '') {
    return ['ok'=>false,'msg'=>'Enter a word','won'=>false];
  }

  $target = $_SESSION['game']['targetWord'];

  // 1) Only letters a-z and within allowed multiset
  if (!preg_match('/^[a-z]+$/', $guess) || !multiset_ok($guess, $target)) {
    $_SESSION['game']['invalidCount']++;
    return ['ok'=>false,'msg'=>'Used disallowed letters.','won'=>false];
  }

  $len = strlen($guess);

  // ✅ 2) WIN CHECK FIRST: exact 7-letter target wins, regardless of dictionary
  if ($len === 7 && $guess === $target) {
    $_SESSION['game']['validGuesses'][] = $guess;
    // optional: add points if you want a bonus for the 7-letter word
    // $_SESSION['game']['score'] += 0;
    return ['ok'=>true,'msg'=>'You found the 7-letter word!','won'=>true];
  }

  // 3) Must be in dictionary (for non-7-letter guesses)
  static $dict = null;
  if ($dict === null) $dict = dict_by_len();
  if (!isset($dict[$len][$guess])) {
    $_SESSION['game']['invalidCount']++;
    return ['ok'=>false,'msg'=>'Not a valid dictionary word.','won'=>false];
  }

  // 4) No duplicates
  if (in_array($guess, $_SESSION['game']['validGuesses'], true)) {
    return ['ok'=>false,'msg'=>'Already guessed that.','won'=>false];
  }

  // 5) Award points by length
  $_SESSION['game']['validGuesses'][] = $guess;
  $_SESSION['game']['score'] += points_for_len($len);
  return ['ok'=>true,'msg'=>'Nice! +' . points_for_len($len) . ' points.','won'=>false];
}

