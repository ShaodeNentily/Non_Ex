<?php
$host = 'localhost';
$db   = 'deine_datenbank';
$user = 'dein_benutzer';
$pass = 'dein_passwort';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "Verbindungsfehler: " . $e->getMessage();
    exit;
}