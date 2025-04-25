<?php
//for sending back to form, if url is used to get this page instead of button
if (!isset($_POST['enroll'])){
  header("Location: enroll_form.php");
  die();
}


?>