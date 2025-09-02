<?php
// admin/delete_transaction.php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$transaction_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$transaction_id) {
    header('Location: dashboard.php');
    exit;
}

// Transaktionsdaten abrufen
$stmt_get = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt_get->execute([$transaction_id]);
$transaction = $stmt_get->fetch();

if ($transaction) {
    $user_id = $transaction['user_id'];
    $amount_to_deduct = $transaction['amount'];

    $pdo->beginTransaction();
    try {
        // 1. Transaktion löschen
        $stmt_delete = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt_delete->execute([$transaction_id]);

        // 2. Guthaben korrigieren (Betrag abziehen)
        $stmt_deduct = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt_deduct->execute([$amount_to_deduct, $user_id]);

        $pdo->commit();
        $_SESSION['message'] = 'Einzahlung gelöscht und Guthaben korrigiert.';
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = 'Fehler: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    header('Location: user_details.php?id=' . $user_id);
} else {
    header('Location: dashboard.php');
}
exit;
?>
