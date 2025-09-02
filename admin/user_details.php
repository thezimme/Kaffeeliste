<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { header('Location: index.php'); exit; }
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id) { header('Location: dashboard.php'); exit; }

$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();
if (!$user) { header('Location: dashboard.php'); exit; }

$bookings = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_time DESC");
$bookings->execute([$user_id]);
$transactions = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_time DESC");
$transactions->execute([$user_id]);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutzerdetails</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">

    <script type="importmap">
    { "imports": { "@material/web/": "https://esm.run/@material/web/" } }
    </script>
    <script type="module">
      import '@material/web/all.js';
      import {styles as typescaleStyles} from '@material/web/typography/md-typescale-styles.js';
      document.adoptedStyleSheets.push(typescaleStyles.styleSheet);
    </script>
</head>
<body>
<main>
    <div class="card user-header-card">
        <a href="dashboard.php" style="text-decoration: none; align-self: flex-start; margin-bottom: 16px;">
            <md-text-button>
                <span class="material-symbols-outlined" slot="icon">arrow_back</span>
                Zurück zum Dashboard
            </md-text-button>
        </a>
        <h1 style="margin-top: 0; text-align: left;">
            <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?>
        </h1>
        <p style="text-align: left; font-size: 1.2em; margin-top: 0; color: var(--md-sys-color-on-surface-variant);">
            Aktuelles Guthaben: 
            <strong class="balance <?= $user['balance'] < 0 ? 'negative' : '' ?>">
                <?= number_format($user['balance'], 2, ',', '.') ?> €
            </strong>
        </p>
    </div>

    <div class="details-grid">
        <div class="card">
            <h2>Kaffee-Buchungen</h2>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead><tr><th>Datum</th><th>Anzahl</th><th style="text-align: right;">Betrag</th><th class="actions">Aktionen</th></tr></thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= date('d.m.Y H:i', strtotime($booking['booking_time'])) ?></td>
                            <td><?= $booking['quantity'] ?></td>
                            <td class="amount-coffee" style="text-align: right;">-<?= number_format($booking['quantity'] * KAFFEE_PREIS, 2, ',', '.') ?> €</td>
                            <td class="actions">
                                <a href="edit_booking.php?id=<?= $booking['id'] ?>">
                                    <md-icon-button>
                                        <span class="material-symbols-outlined">edit</span>
                                    </md-icon-button>
                                </a>
                                <a href="delete_booking.php?id=<?= $booking['id'] ?>" onclick="return confirm('Sicher?')">
                                    <md-icon-button>
                                        <span class="material-symbols-outlined">delete</span>
                                    </md-icon-button>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <h2>Guthaben-Einzahlungen</h2>
             <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead><tr><th>Datum</th><th>Beschreibung</th><th style="text-align: right;">Betrag</th><th class="actions">Aktionen</th></tr></thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= date('d.m.Y H:i', strtotime($transaction['transaction_time'])) ?></td>
                            <td><?= htmlspecialchars($transaction['description']) ?></td>
                            <td class="amount-deposit" style="text-align: right;">+<?= number_format($transaction['amount'], 2, ',', '.') ?> €</td>
                            <td class="actions">
                                 <a href="edit_transaction.php?id=<?= $transaction['id'] ?>">
                                    <md-icon-button>
                                        <span class="material-symbols-outlined">edit</span>
                                    </md-icon-button>
                                 </a>
                                <a href="delete_transaction.php?id=<?= $transaction['id'] ?>" onclick="return confirm('Sicher?')">
                                    <md-icon-button>
                                        <span class="material-symbols-outlined">delete</span>
                                    </md-icon-button>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</body>
</html>
