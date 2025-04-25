<?php
//for sending back to form, if url is used to get this page instead of button
if (!isset($_POST['login'])){
  header("Location: login_form.php");
  die();
}


?>