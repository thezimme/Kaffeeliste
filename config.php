<?php
// config.php

// --- Datenbank-Einstellungen ---
define('DB_HOST', 'db5018540677.hosting-data.io'); // Meistens 'localhost'
define('DB_NAME', 'Kaffee'); // Name deiner Datenbank
define('DB_USER', 'dbu4323536'); // Dein Datenbank-Benutzer
define('DB_PASS', 'BSIKaffee25FTL'); // Dein Datenbank-Passwort
define('DB_CHARSET', 'utf8mb4');

// --- Anwendungs-Einstellungen ---
define('KAFFEE_PREIS', 0.30); // Preis pro Kaffee in Euro
define('ADMIN_PASSWORD', 'dein_sicheres_admin_passwort'); // Ändere dieses Passwort!

// --- Datenbankverbindung herstellen (PDO) ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
