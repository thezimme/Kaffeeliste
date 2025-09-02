<?php
// config.php

// --- Datenbank-Einstellungen ---
// Es wird dringend empfohlen, diese sensiblen Daten als Umgebungsvariablen
// zu speichern und nicht direkt im Code zu hinterlegen.
define('DB_HOST', getenv('DB_HOST') ?: 'db5018540677.hosting-data.io');
define('DB_NAME', getenv('DB_NAME') ?: 'dbs14718939');
define('DB_USER', getenv('DB_USER') ?: 'dbu4323536');
define('DB_PASS', getenv('DB_PASS') ?: 'BSIKaffee25FTL');
define('DB_CHARSET', 'utf8mb4');

// --- Anwendungs-Einstellungen ---
define('KAFFEE_PREIS', 0.30); // Preis pro Kaffee in Euro

// --- Admin Passwort ---
// Speichere niemals Passwörter im Klartext! Nutze stattdessen password_hash().
// Das Passwort hier ist 'dein_sicheres_admin_passwort'
define('ADMIN_PASSWORD', '$2y$10$fQGJm2W1kT4mcA7jBr0BJeTj2vg/7WwySg3B67xF/EeIJ1bxbgqvW');

// --- Datenbankverbindung herstellen (PDO) ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // Im produktiven Einsatz sollte hier ein Logging stattfinden,
    // anstatt die Fehlermeldung direkt auszugeben.
    error_log($e->getMessage());
    // Zeige eine allgemeine Fehlermeldung an den Benutzer
    die('Ein Fehler mit der Datenbankverbindung ist aufgetreten.');
}
?>
