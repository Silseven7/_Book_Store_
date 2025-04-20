<?php
require "header.php";
?>

<div class="bg-white text-black p-6 rounded-lg shadow-md max-w-md w-full z-10 relative">
  <form action="login.php" method="POST">

    <label>Username:</label>
    <input type="text" name="username" class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>

    <input type="submit" name="submit" value="Submit"  class="text-black bg-white px-3 py-2 rounded-lg shadow-md border border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <br><br>
  </form>

  <a href="index.php"> Go back(click me) </a>
</div>

</body>
</html>