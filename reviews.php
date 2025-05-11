<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
  header("Location: auth/login_form.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Reviews</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-purple-50 min-h-screen px-4 py-6">

  <div class="max-w-4xl mx-auto">
    <h1 class="text-4xl font-bold text-center mb-8 text-purple-700">✍️ My Reviews</h1>

    <div class="space-y-6">
      
      <?php for ($i = 1; $i <= 3; $i++): ?>
        <div class="bg-white p-6 rounded-xl shadow-md">
          <h2 class="text-2xl font-semibold">Book Title <?= $i ?></h2>
          <p class="text-sm text-gray-600 mb-2">By Author Name</p>
          <div class="text-yellow-400 text-xl mb-2">⭐⭐⭐⭐☆</div>
          <p class="text-gray-700">This book was an engaging read. I loved the characters and pacing, especially the way the plot unfolded around chapter 5...</p>
          <button class="mt-2 text-sm text-blue-600 hover:underline">Edit Review</button>
        </div>
      <?php endfor; ?>
    </div>
  </div>

</body>
</html>
