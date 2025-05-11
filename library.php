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
  <title>My Library</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-yellow-50 min-h-screen px-4 py-6">

  <div class="max-w-5xl mx-auto">
    <h1 class="text-4xl font-bold text-center mb-8 text-green-700">📚 My Library</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="bg-white p-6 rounded-xl shadow-md flex items-start gap-4">
          <img src="https://source.unsplash.com/100x150/?book,<?= $i ?>" alt="Book" class="rounded shadow">
          <div>
            <h3 class="text-xl font-bold">Book Title <?= $i ?></h3>
            <p class="text-gray-600">Author Name</p>
            <p class="mt-2 text-sm">Status: <span class="font-semibold text-blue-600">Reading</span></p>
            <button class="mt-2 px-4 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">Remove</button>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>

</body>
</html>
