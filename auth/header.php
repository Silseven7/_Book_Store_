<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?? 'Bookstore' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/gsap@3/dist/gsap.min.js"></script>
  <script src="https://unpkg.com/gsap@3/dist/ScrollTrigger.min.js"></script>
  <style>
    .back-to-dashboard {
      position: fixed;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 15px 10px;
      border-radius: 0 10px 10px 0;
      text-decoration: none;
      transition: all 0.3s ease;
      z-index: 1000;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .back-to-dashboard:hover {
      padding-right: 20px;
      background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
      box-shadow: 4px 0 8px rgba(0, 0, 0, 0.2);
    }

    .back-to-dashboard .text {
      display: none;
      margin-left: 10px;
      white-space: nowrap;
    }

    .back-to-dashboard:hover .text {
      display: inline;
    }

    .back-to-dashboard i {
      font-size: 1.2rem;
    }
  </style>
</head>
<body class="bg-cover bg-center text-white min-h-screen flex items-center justify-center"
      style="background-image: url('https://wallpapercave.com/wp/wp6974213.jpg');">
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_path = $_SERVER['REQUEST_URI'];
    $is_dashboard = $current_page === 'dashboard.php' || strpos($current_path, '/dashboard') !== false;
    $is_landing = $current_page === 'landing_page.php';
    $is_login = $current_page === 'login_form.php';
    $is_enroll = $current_page === 'enroll_form.php';
    
    if (isset($_SESSION['logged_in']) && !$is_dashboard && !$is_landing && !$is_login && !$is_enroll): 
    ?>
    <a href="/_Book_Store_/dashboard" class="back-to-dashboard">
        <i class="fas fa-arrow-left"></i>
        <span class="text">Back to Dashboard</span>
    </a>
    <?php endif; ?>