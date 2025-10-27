<?php
function user_find_by_email($db, $email) {
  $res = pg_query_params($db, "SELECT id,name,email,pass_hash FROM hw3_users WHERE email=$1", [$email]);
  return $res ? pg_fetch_assoc($res) : null;
}
function user_create($db, $name, $email, $hash) {
  $res = pg_query_params($db, "INSERT INTO hw3_users(name,email,pass_hash) VALUES($1,$2,$3) RETURNING id",
                         [$name,$email,$hash]);
  $row = $res ? pg_fetch_assoc($res) : null;
  return $row ? (int)$row['id'] : 0;
}
