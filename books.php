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
  <title>Browse Books</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen px-4 py-6">

  <div class="max-w-6xl mx-auto">
    <h1 class="text-4xl font-bold text-center mb-8 text-indigo-700">📖 Browse Books</h1>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php for ($i = 1; $i <= 8; $i++): ?>
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition">
          <img src="https://source.unsplash.com/200x300/?book,<?= $i ?>" alt="Book Cover" class="w-full h-60 object-cover">
          <div class="p-4">
            <h2 class="text-lg font-semibold mb-1">Book Title <?= $i ?></h2>
            <p class="text-sm text-gray-600 mb-2">Author Name</p>
            <button class="mt-2 px-4 py-1 bg-green-500 text-white rounded hover:bg-green-600">Add to Library</button>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>

</body>
</html>
