<?php
session_start();
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
  header("Location: auth/login_form.php");
}

echo "Welcome to the Book Store";

?>