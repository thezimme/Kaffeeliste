<?php
// admin/guthaben_buchen.php
require_once '../config.php';
session_start();

// Sicherheits-Check: Ist der Admin eingeloggt und wurde das Formular gesendet?
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id = (int)$_POST['user_id'];
$amount = (float)str_replace(',', '.', $_POST['amount']); // Komma zu Punkt umwandeln

if ($user_id > 0 && $amount > 0) {
    $pdo->beginTransaction();
    try {
        // 1. Guthaben beim Nutzer aktualisieren
        $stmt_update = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt_update->execute([$amount, $user_id]);

        // 2. Transaktion protokollieren
        $stmt_log = $pdo->prepare("INSERT INTO transactions (user_id, amount, description) VALUES (?, ?, ?)");
        $stmt_log->execute([$user_id, $amount, 'Einzahlung durch Admin']);
        
        $pdo->commit();

        $_SESSION['message'] = 'Guthaben erfolgreich gebucht.';
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = 'Fehler beim Buchen: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
} else {
    $_SESSION['message'] = 'Ungültige Eingabe.';
    $_SESSION['message_type'] = 'error';
}

header('Location: dashboard.php');
exit;
?>
