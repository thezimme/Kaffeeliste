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

// Kaffee-Buchungen des Nutzers abrufen
$bookings = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_time DESC");
$bookings->execute([$user_id]);

// Guthaben-Transaktionen des Nutzers abrufen
$transactions = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_time DESC");
$transactions->execute([$user_id]);

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutzerdetails</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

<main>
    <div class="card">
        <a href="dashboard.php" style="text-decoration: none; color: var(--primary-color);">&larr; Zurück zum Dashboard</a>
        <h1 style="margin-top: 16px;">Details für <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></h1>
        <p style="text-align: center; font-size: 1.2em; margin-bottom: 0;">Aktuelles Guthaben: <strong class="<?= $user['balance'] < 0 ? 'balance negative' : '' ?>"><?= number_format($user['balance'], 2, ',', '.') ?> €</strong></p>
    </div>

    <div class="details-grid">
        <div class="card">
            <h2>Kaffee-Buchungen</h2>
            <table class="transaction-list">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Anzahl</th>
                        <th>Referat</th>
                        <th style="text-align: right;">Betrag</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= date('d.m.Y H:i', strtotime($booking['booking_time'])) ?></td>
                        <td><?= $booking['quantity'] ?></td>
                        <td><?= htmlspecialchars($booking['reference']) ?></td>
                        <td class="amount-coffee" style="text-align: right;">-<?= number_format($booking['quantity'] * KAFFEE_PREIS, 2, ',', '.') ?> €</td>
                        <td class="actions">
                            <a href="edit_booking.php?id=<?= $booking['id'] ?>">✏️</a>
                            <a href="delete_booking.php?id=<?= $booking['id'] ?>" onclick="return confirm('Sicher?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Guthaben-Einzahlungen</h2>
            <table class="transaction-list">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Beschreibung</th>
                        <th style="text-align: right;">Betrag</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= date('d.m.Y H:i', strtotime($transaction['transaction_time'])) ?></td>
                        <td><?= htmlspecialchars($transaction['description']) ?></td>
                        <td class="amount-deposit" style="text-align: right;">+<?= number_format($transaction['amount'], 2, ',', '.') ?> €</td>
                        <td class="actions">
                             <a href="edit_transaction.php?id=<?= $transaction['id'] ?>">✏️</a>
                             <a href="delete_transaction.php?id=<?= $transaction['id'] ?>" onclick="return confirm('Sicher?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
