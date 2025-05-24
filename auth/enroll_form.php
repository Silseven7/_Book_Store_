<?php
require "header.php";

$error_message = "";

if (isset($_POST['enroll']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require __DIR__ . '/../validators.php';

  $username = $_POST['username'];
  $real_name = $_POST['real_name'];
  $password = $_POST['password'];
  $email = $_POST['email'];

  if (is_input_empty($real_name, $username, $password, $email)) {
    $error_message = "⚠️ All fields are required.";
  } elseif (is_email_invalid($email)) {
    $error_message = "⚠️ Invalid email format.";
  } elseif (does_username_exist($pdo, $username)) {
    $error_message = "⚠️ Username is already taken.";
  } elseif (is_email_taken($pdo, $email)) {
    $error_message = "⚠️ Email is already registered.";
  } else {
    $password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (real_name, username, password, email)
                           VALUES (:real_name, :username, :password, :email)");
    $stmt->bindParam(':real_name', $real_name);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':email', $email);

    if ($stmt->execute()) {
      $_SESSION['enroll_success'] = true;
      header("Location: /_Book_Store_/login_form");
      exit;
    } else {
      $error_message = "❌ Something went wrong. Please try again.";
    }
  }
}
?>

<div class="bg-white text-black p-8 rounded-2xl shadow-2xl w-full max-w-md relative z-10">
  <h2 class="text-2xl font-semibold mb-6 text-center">📝 Enroll to ShelfShare</h2>

  <?php if (!empty($error_message)): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4">
      <?= $error_message ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['enroll_success'])): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4">
      🎉 User enrolled successfully! Please log in.
    </div>
    <?php unset($_SESSION['enroll_success']); ?>
  <?php endif; ?>

  <form action="/_Book_Store_/enroll_form" method="POST" class="space-y-5">
    <div>
      <label class="block text-sm font-medium">Real Name</label>
      <input type="text" name="real_name" required
             class="w-full px-4 py-2 mt-1 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
    </div>

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

    <div>
      <label class="block text-sm font-medium">Email</label>
      <input type="email" name="email" required
             class="w-full px-4 py-2 mt-1 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
    </div>

    <button type="submit" name="enroll"
            class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
      Enroll
    </button>
  </form>

  <p class="mt-6 text-center text-sm">
    Already have an account?
    <a href="/_Book_Store_/login_form" class="text-blue-600 hover:underline">Login here</a>
  </p>

  <p class="mt-2 text-center text-sm">
    <a href="/_Book_Store_/landing_page" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> ⬅ Previous Page
    </a>
  </p>
</div>

</body>
</html>

