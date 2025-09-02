<?php
// admin/delete_booking.php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    header('Location: dashboard.php');
    exit;
}

// Buchungsdaten abrufen, bevor wir sie löschen
$stmt_get = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt_get->execute([$booking_id]);
$booking = $stmt_get->fetch();

if ($booking) {
    $user_id = $booking['user_id'];
    $cost_to_refund = $booking['quantity'] * KAFFEE_PREIS;

    $pdo->beginTransaction();
    try {
        // 1. Buchung löschen
        $stmt_delete = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt_delete->execute([$booking_id]);

        // 2. Guthaben wieder gutschreiben
        $stmt_refund = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt_refund->execute([$cost_to_refund, $user_id]);

        $pdo->commit();
        $_SESSION['message'] = 'Buchung gelöscht und Guthaben erstattet.';
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
