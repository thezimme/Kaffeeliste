<?php
// admin/user_details.php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id) {
    header('Location: dashboard.php');
    exit;
}

// Nutzerdaten abrufen
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

if (!$user) {
    header('Location: dashboard.php');
    exit;
}

// Buchungen des Nutzers abrufen
$bookings = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_time DESC");
$bookings->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutzerdetails</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<main>
    <div class="card">
        <a href="dashboard.php">&larr; Zurück zum Dashboard</a>
        <h1>Details für <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></h1>
        <p style="text-align: center; font-size: 1.2em;">Aktuelles Guthaben: <strong><?= number_format($user['balance'], 2, ',', '.') ?> €</strong></p>
    </div>

    <div class="card">
        <h2>Alle Buchungen</h2>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Anzahl</th>
                    <th>Referat</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= date('d.m.Y H:i', strtotime($booking['booking_time'])) ?></td>
                    <td><?= $booking['quantity'] ?></td>
                    <td><?= htmlspecialchars($booking['reference']) ?></td>
                    <td>
                        <a href="edit_booking.php?id=<?= $booking['id'] ?>" class="button-small">Ändern</a>
                        <a href="delete_booking.php?id=<?= $booking['id'] ?>" onclick="return confirm('Sicher?')" class="button-small danger">Löschen</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
