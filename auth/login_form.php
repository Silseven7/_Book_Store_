<?php
require "header.php";

$error_message = "";

if (isset($_POST['login']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require __DIR__ . '/../validators.php';

  $username = $_POST['username'];
  $password = $_POST['password'];

  if (!does_username_exist($pdo, $username)) {
    $error_message = "❌ Username is incorrect, try again.";
  } elseif (!does_password_exist($pdo, $password, $username)) {
    $error_message = "❌ Password is incorrect, try again.";
  } else {
    session_regenerate_id(true);
    $_SESSION['logged_in'] = true;
    $_SESSION['real_name'] = $_POST['real_name'];
    $_SESSION['username'] = $_POST['username'];
    header("Location: /_Book_Store_/dashboard");
    exit;
  }
}
?>

<div class="bg-white text-black p-8 rounded-2xl shadow-2xl w-full max-w-md relative z-10">
  <h2 class="text-2xl font-semibold mb-6 text-center">🔐 Login to Bookstore</h2>

  <?php if (!empty($error_message)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
      <?= $error_message ?>
    </div>
  <?php endif; ?>

  <form action="/_Book_Store_/login_form" method="POST" class="space-y-5">
    <div>
      <label class="block text-sm font-medium">Username</label>
      <input type="text" name="username" required
             class="w-full px-4 py-2 mt-1 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
    </div>

    <div>
      <label class="block text-sm font-medium">Password</label>
      <input type="password" name="password" required
             class="w-full px-4 py-2 mt-1 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
    </div>

    <button type="submit" name="login"
            class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
      Log In
    </button>
  </form>

  <p class="mt-6 text-center text-sm">
    Don't have an account?
    <a href="/_Book_Store_/enroll_form" class="text-blue-600 hover:underline">Enroll here</a>
  </p>

  <p class="mt-2 text-center text-sm">
    <a href="/_Book_Store_/landing_page" class="text-gray-500 hover:underline">⬅ Back to Home</a>
  </p>
</div>

</body>
</html>

