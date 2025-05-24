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

    '/add_book' => 'dashboard_pages/add_book.php',
    '/book_details' => 'dashboard_pages/book_details.php',
    '/books' => 'dashboard_pages/books.php',
    '/browse_books' => 'dashboard_pages/browse_books.php',
    '/dashboard' => 'dashboard_pages/dashboard.php',
    '/edit_book' => 'dashboard_pages/edit_book.php',
    '/library' => 'dashboard_pages/library.php',
    '/logout' => 'dashboard_pages/logout.php',
    '/reading_list' => 'dashboard_pages/reading_list.php',
    '/reviews' => 'dashboard_pages/reviews.php',

    '/profile' => 'dashboard_pages/profile.php',
    '/shelves' => 'dashboard_pages/shelves.php',
    '/reading_list' => 'dashboard_pages/reading_list.php',

    '/delete_book' => 'api/delete_book.php',
    '/removed_book' => 'api/removed_book.php',
    '/remove_reading_status' => 'api/remove_reading_status.php',
    '/save_book' => 'api/save_book.php',
    '/submit_review' => 'api/submit_review.php',
    '/update_reading_status' => 'api/update_reading_status.php',
];

$auth_routes = ['/landing_page', '/header', '/enroll_form', '/login_form'];
$dashboard_routes = [
    '/dashboard', '/books', '/book_details', '/add_book', '/browse_books', '/edit_book',
    '/library', '/logout', '/reviews', '/profile', 
    '/shelves', '/reading_list'
];
$api_routes = [];

// Check if trying to access dashboard routes
if (in_array($uri, $dashboard_routes)) {
    // If not logged in, redirect to landing page
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: ' . $base_path . '/landing_page');
        exit;
    }
}

// Check if trying to access auth routes
if (in_array($uri, $auth_routes)) {
    // If already logged in, redirect to dashboard
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        header('Location: ' . $base_path . '/dashboard');
        exit;
    }
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