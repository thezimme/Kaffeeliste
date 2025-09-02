<?php
// admin/edit_booking.php
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

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buchung bearbeiten</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<main>
    <div class="card">
        <h1>Buchung bearbeiten</h1>
        <form action="update_booking.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
            <input type="hidden" name="user_id" value="<?= $booking['user_id'] ?>">
            <input type="hidden" name="old_quantity" value="<?= $booking['quantity'] ?>">
            
            <md-outlined-text-field label="Anzahl Kaffee" type="number" name="quantity" required value="<?= $booking['quantity'] ?>"></md-outlined-text-field>
            
            <md-filled-button type="submit">
                <span class="material-symbols-outlined" slot="icon">save</span>
                Speichern
            </md-filled-button>
        </form>
    </div>
</main>
</body>
</html>
