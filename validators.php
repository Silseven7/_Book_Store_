<?php
require "database.php";

function is_input_empty($real_name, $username, $password, $email){
  return empty(trim($real_name)) || empty(trim($username)) || empty(trim($password)) || empty(trim($email));
}

function is_email_invalid($email){
  return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

function does_username_exist($pdo, $username){
  return get_username($pdo, $username) !== false;
}

function is_email_taken($pdo, $email){
  return get_email($pdo, $email) !== false;
}

function does_password_exist($pdo, $password, $username){
  $user = get_user_info($pdo, $username);
  return $user && password_verify($password, $user['password']);
}

?> 