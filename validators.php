<?php
require "database.php";

function is_input_empty($real_name, $username, $password, $email){
  return empty(trim($real_name)) || empty(trim($username)) || empty(trim($password)) || empty(trim($email));
}

function is_email_invalid($email){
  return !filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_username_taken($pdo, $username){
  return get_username($pdo, $username) == $username;
}

function is_email_taken($pdo, $email){
  return get_email($pdo, $email) == $email;
}

?>