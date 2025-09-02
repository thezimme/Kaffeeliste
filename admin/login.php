<?php
// admin/login.php
require_once '../config.php'; // ../ geht einen Ordner nach oben
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    // Passwort prüfen
    if ($password === ADMIN_PASSWORD) {
        // Passwort ist korrekt, Session-Variable setzen
        $_SESSION['admin_logged_in'] = true;
        unset($_SESSION['login_error']);
        header('Location: dashboard.php');
        exit;
    } else {
        // Passwort ist falsch
        $_SESSION['login_error'] = 'Falsches Passwort!';
        header('Location: index.php');
        exit;
    }
} else {
    // Kein POST-Request, zurück zum Login
    header('Location: index.php');
    exit;
}
?>
