<?php
// admin/update_transaction.php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$transaction_id = (int)$_POST['transaction_id'];
$user_id = (int)$_POST['user_id'];
$new_amount = (float)$_POST['amount'];
$old_amount = (float)$_POST['old_amount'];
$description = trim($_POST['description']);

$amount_difference = $new_amount - $old_amount;

$pdo->beginTransaction();
try {
    // 1. Transaktion aktualisieren
    $stmt_update_trans = $pdo->prepare("UPDATE transactions SET amount = ?, description = ? WHERE id = ?");
    $stmt_update_trans->execute([$new_amount, $description, $transaction_id]);

    // 2. Guthaben des Nutzers anpassen
    $stmt_update_balance = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt_update_balance->execute([$amount_difference, $user_id]);
    
    $pdo->commit();
    $_SESSION['message'] = 'Einzahlung erfolgreich aktualisiert.';
    $_SESSION['message_type'] = 'success';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = 'Fehler: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header('Location: user_details.php?id=' . $user_id);
exit;
?>
