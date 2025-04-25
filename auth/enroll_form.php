<!-- Validation for inputs, will be removed to a different page once Ajax implemented -->
<?php
  $error_message = "";
  if(isset($_POST['enroll']) && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = $_POST['username'];
    $real_name = $_POST['real_name'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    $flag = 0;

    require "../validators.php";

    
    if(is_email_invalid($email)){
      $error_message = "Email is not valid, try again...";
      $flag = 1;
    }
    if(is_username_taken($pdo, $username)){
      $error_message = "Username is taken, try different username";
      $flag = 1;
    }
    if(is_email_taken($pdo, $email)){
      $error_message = "Email is not valid, try different email";
      $flag = 1;
    }
    if(is_input_empty($real_name, $username, $password, $email)){
      $error_message = "All input fields are required...";
      $flag = 1;
    }
  
  }

?>

<?php
require "header.php";
?>

<div class="bg-white text-black p-6 rounded-lg shadow-md max-w-md w-full z-10 relative">

  <p><?php echo $error_message; ?></p> <br>

  <form action="enroll_form.php" method="POST">
    <label>Real Name:</label>
    <input type="text" name="real_name" class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>

    <label>Username:</label>
    <input type="text" name="username" class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>

    <label>Email:</label>
    <input type="text" name="email" class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>

    <input type="submit" name="enroll" value="Enroll" class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>
  </form>
  
  <a href="../">Go back (click me)</a>
</div>

</body>
</html>
