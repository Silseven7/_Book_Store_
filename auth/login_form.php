<?php
require "header.php";
?>
  <!-- Validation for inputs, will be removed to a different page once Ajax implemented -->
  <?php
  $error_message = "";
  if(isset($_POST['login']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = $_POST['username'];
    $password = $_POST['password'];
   
    $flag = 0;

    require "../validators.php";
   
    if(!does_username_exist($pdo, $username)){
      $error_message = "Username is incorrect, try again";
      $flag = 1;
    }
    else if(!does_password_exist($pdo, $password, $username)){
      $error_message = "Password is incorrect, try again";
      $flag = 1;
    }
   
    if($flag == 0){
      session_start();
      $_SESSION['logged_in'] = true;
      header("Location: ../dashboard.php");
      die();
    }

  }

  ?>

<div class="bg-white text-black p-6 rounded-lg shadow-md max-w-md w-full z-10 relative">
  <p><?php echo "$error_message"; ?></p> <br>
  
  <form action="login_form.php" method="POST">

    <label>Username:</label>
    <input type="text" name="username" class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>

    <input type="submit" name="login" value="Login"  class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>
  </form>

  <a href="../"> Go back(click me) </a>
</div>

</body>
</html>