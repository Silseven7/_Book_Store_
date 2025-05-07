<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: auth/login_form.php");
  exit;
}

$real_name = $_SESSION['real_name'] ?? $_SESSION['username'] ?? 'Bookstore User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Bookstore Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-purple-200 via-pink-200 to-yellow-100 min-h-screen flex items-center justify-center">

  <div class="bg-white text-gray-800 p-10 rounded-3xl shadow-2xl w-full max-w-xl text-center space-y-6">
    <h1 class="text-4xl font-extrabold text-purple-700">📚 Welcome, <?= htmlspecialchars($real_name) ?>!</h1>

    <p class="text-lg">You're logged in. Ready to explore?</p>

    <div class="grid grid-cols-1 gap-4">
      <a href="books.php"
         class="py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
        📖 Browse Books
      </a>

      <a href="orders.php"
         class="py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
        🧾 View Your Orders
      </a>

      <form action="auth/logout.php" method="POST">
        <button type="submit"
                class="w-full py-3 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition">
          🚪 Logout
        </button>
      </form>
    </div>
  </div>

</body>
</html>

