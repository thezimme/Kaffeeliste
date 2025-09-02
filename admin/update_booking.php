<?php
// admin/update_booking.php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$booking_id = (int)$_POST['booking_id'];
$user_id = (int)$_POST['user_id'];
$new_quantity = (int)$_POST['quantity'];
$old_quantity = (int)$_POST['old_quantity'];

// Die Kosten-Differenz berechnen
$cost_difference = ($new_quantity - $old_quantity) * KAFFEE_PREIS;

$pdo->beginTransaction();
try {
    // 1. Buchung aktualisieren (nur die Menge)
    $stmt_update_booking = $pdo->prepare("UPDATE bookings SET quantity = ? WHERE id = ?");
    $stmt_update_booking->execute([$new_quantity, $booking_id]);

    // 2. Guthaben des Nutzers anpassen
    $stmt_update_balance = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmt_update_balance->execute([$cost_difference, $user_id]);
    
    $pdo->commit();
    $_SESSION['message'] = 'Buchung erfolgreich aktualisiert.';
    $_SESSION['message_type'] = 'success';
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = 'Fehler: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header('Location: user_details.php?id=' . $user_id);
exit;
?>
