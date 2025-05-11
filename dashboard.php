<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: auth/login_form.php");
  exit;
}

$real_name = $_SESSION['real_name'] ?? $_SESSION['username'] ?? 'Book Lover';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>BookNest Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100 min-h-screen flex items-center justify-center">

  <div class="bg-white text-gray-800 p-10 rounded-3xl shadow-2xl w-full max-w-3xl space-y-8">
    <div class="text-center space-y-2">
      <h1 class="text-4xl font-bold text-indigo-700">👋 Hello, <?= htmlspecialchars($real_name) ?>!</h1>
      <p class="text-lg text-gray-600">Welcome back to your BookNest. What would you like to explore today?</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <a href="books.php" class="bg-blue-600 text-white py-5 px-4 rounded-2xl shadow-lg hover:bg-blue-700 transition text-center">
        📖 Browse Books
      </a>

      <a href="library.php" class="bg-green-500 text-white py-5 px-4 rounded-2xl shadow-lg hover:bg-green-600 transition text-center">
        📚 My Library
      </a>

      <a href="reviews.php" class="bg-yellow-500 text-white py-5 px-4 rounded-2xl shadow-lg hover:bg-yellow-600 transition text-center">
        ✍️ My Reviews
      </a>
    </div>

    <form action="auth/logout.php" method="POST" class="text-center mt-6">
      <button type="submit" class="py-2 px-6 bg-red-500 text-white rounded-full hover:bg-red-600 transition">
        🚪 Logout
      </button>
    </form>
  </div>

</body>
</html>

