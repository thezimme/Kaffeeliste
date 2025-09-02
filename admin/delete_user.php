<?php
// admin/delete_user.php
require_once '../config.php';
session_start();

// Sicherheits-Check: Ist der Admin eingeloggt und wurde das Formular gesendet?
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

if ($user_id) {
    $pdo->beginTransaction();
    try {
        // 1. Alle Buchungen des Nutzers löschen
        $stmt_bookings = $pdo->prepare("DELETE FROM bookings WHERE user_id = ?");
        $stmt_bookings->execute([$user_id]);

        // 2. Alle Transaktionen des Nutzers löschen
        $stmt_transactions = $pdo->prepare("DELETE FROM transactions WHERE user_id = ?");
        $stmt_transactions->execute([$user_id]);

        // 3. Den Nutzer selbst löschen
        $stmt_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt_user->execute([$user_id]);
        
        $pdo->commit();

        $_SESSION['message'] = 'Nutzer und alle zugehörigen Daten wurden erfolgreich gelöscht.';
        $_SESSION['message_type'] = 'success';

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = 'Fehler beim Löschen des Nutzers: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
} else {
    $_SESSION['message'] = 'Ungültige Anfrage.';
    $_SESSION['message_type'] = 'error';
}

header('Location: dashboard.php');
exit;
?>
