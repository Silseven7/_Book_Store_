<?php 
// Basic session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base path as it appears in the URL (adjust if needed)
$base_path = '/_Book_Store_';

// Parse URI and remove base path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim(str_replace($base_path, '', $uri), '/');
if ($uri === '') {
    $uri = '/';
}

// Define routes
$routes = [
    '/' => 'auth/landing_page.php',
    '/database' => 'database.php',
    '/validators' => 'validators.php',

    '/landing_page' => 'auth/landing_page.php',
    '/header' => 'auth/header.php',
    '/enroll_form' => 'auth/enroll_form.php',
    '/login_form' => 'auth/login_form.php',

    '/books' => 'dashboard_pages/books.php',
    '/book_details' => 'dashboard_pages/book_details.php',
    '/add_book' => 'dashboard_pages/add_book.php',
    '/edit_book' => 'dashboard_pages/edit_book.php',
    '/dashboard' => 'dashboard_pages/dashboard.php',
    '/library' => 'dashboard_pages/library.php',
    '/logout' => 'dashboard_pages/logout.php',
    '/reviews' => 'dashboard_pages/reviews.php',
    '/profile' => 'dashboard_pages/profile.php',
    '/shelves' => 'dashboard_pages/shelves.php',
    '/reading_list' => 'dashboard_pages/reading_list.php'
];

$auth_routes = ['/landing_page', '/header', '/enroll_form', '/login_form'];
$dashboard_routes = [
    '/dashboard', '/books', '/book_details', '/add_book', '/edit_book',
    '/library', '/logout', '/reviews', '/profile', 
    '/shelves', '/reading_list'
];

// Redirect to login if trying to access dashboard and not logged in
if (in_array($uri, $dashboard_routes) && (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != true)) {
    header('Location: ' . $base_path . '/landing_page');
    exit;
}

// Redirect to dashboard if logged in and trying to access auth pages
if (in_array($uri, $auth_routes) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
    header('Location: ' . $base_path . '/dashboard');
    exit;
}

// load the page if the route exists
if (array_key_exists($uri, $routes)) {
    require __DIR__ . '/' . $routes[$uri];
    exit;
} else {
    http_response_code(404);
    echo 'Page not found.';
    exit;
}
?>