<?php
$host = 'localhost';
$port = '5432';
$dbname = 'classwall';
$username = 'postgres';
$password = 'kyokoanime1970';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Could not connect to the database. Check your db.php settings.');
}
?>