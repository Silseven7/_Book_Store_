<?php
//below configiration belongs to database in db4free.net
//from https://www.db4free.net/phpMyAdmin/ you can login,
//with username + password(same as below) and view database + table content and make queries

$host = "db4free.net";
$dbname = "book_db_w";
$username = "book_store__";
$password = "bookphp123";
$port = 3306;

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    
    // Set error mode to exception for better debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // echo "Connected successfully!"; //uncomment when trying to test connection
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function get_username($pdo, $username){
    $query = "SELECT username FROM users WHERE username = :username;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":username",$username);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function get_email($pdo, $email){
    $query = "SELECT email FROM users WHERE email = :email;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function get_password($pdo, $password){
    $query = "SELECT password FROM users WHERE password = :password;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":password", $password);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}
?>