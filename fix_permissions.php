<?php
// Check and fix permissions for sessions directory
$session_dir = __DIR__ . '/sessions';

// Create sessions directory if it doesn't exist
if (!file_exists($session_dir)) {
    if (!mkdir($session_dir, 0777, true)) {
        die("Failed to create sessions directory. Please create it manually with write permissions.");
    }
    echo "Created sessions directory.\n";
}

// Check and fix permissions
if (!is_writable($session_dir)) {
    if (!chmod($session_dir, 0777)) {
        die("Failed to set permissions on sessions directory. Please set permissions manually to 777.");
    }
    echo "Fixed permissions on sessions directory.\n";
}

// Check if PHP can write to the directory
$test_file = $session_dir . '/test.txt';
if (file_put_contents($test_file, 'test')) {
    unlink($test_file);
    echo "PHP can write to sessions directory.\n";
} else {
    die("PHP cannot write to sessions directory. Please check permissions.");
}

echo "All permissions are set correctly.\n";
?> 