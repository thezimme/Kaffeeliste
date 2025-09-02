<?php
// index.php Final
require_once 'config.php';
session_start();

$user_data = null;
$user_names = [];
if (isset($_COOKIE['coffee_user'])) {
    $user_names_from_cookie = json_decode($_COOKIE['coffee_user'], true);
    if ($user_names_from_cookie && isset($user_names_from_cookie['firstname']) && isset($user_names_from_cookie['lastname'])) {
        $user_names = $user_names_from_cookie;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE firstname = ? AND lastname = ?");
        $stmt->execute([$user_names['firstname'], $user_names['lastname']]);
        $user_data = $stmt->fetch();
        if ($user_data) {
            $stmt_bookings = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_time DESC LIMIT 2");
            $stmt_bookings->execute([$user_data['id']]);
            $user_data['bookings'] = $stmt_bookings->fetchAll();
            $stmt_transaction = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND description = 'Einzahlung durch Admin' ORDER BY transaction_time DESC LIMIT 1");
            $stmt_transaction->execute([$user_data['id']]);
            $user_data['last_deposit'] = $stmt_transaction->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaffeeliste</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <script type="importmap">
    {
      "imports": {
        "@material/web/": "https://esm.run/@material/web/"
      }
    }
    </script>

    <script type="module">
      import '@material/web/all.js';
      import {styles as typescaleStyles} from '@material/web/typography/md-typescale-styles.js';
      document.adoptedStyleSheets.push(typescaleStyles.styleSheet);
    </script>

    <style>
        /* Zusätzliche Styles, die nach den Komponenten geladen werden müssen */
        md-outlined-select, md-filled-button {
            width: 100%;
        }
    </style>
</head>
<body>

<main>
    <div class="card">
        <h1>☕ Kaffeeliste</h1>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . $_SESSION['message_type'] . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message'], $_SESSION['message_type']);
        }
        ?>

        <form action="buchen.php" method="post" style="display: flex; flex-direction: column; gap: 20px;">
            <md-outlined-text-field label="Vorname" name="firstname" required value="<?= htmlspecialchars($user_names['firstname'] ?? '') ?>"></md-outlined-text-field>
            <md-outlined-text-field label="Nachname" name="lastname" required value="<?= htmlspecialchars($user_names['lastname'] ?? '') ?>"></md-outlined-text-field>
            <md-outlined-text-field label="Referat / Abteilung" name="reference" required value="<?= htmlspecialchars($user_names['reference'] ?? '') ?>"></md-outlined-text-field>

            <md-outlined-select label="Anzahl Kaffee" name="quantity">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <md-select-option value="<?= $i ?>" <?= ($i == 1) ? 'selected' : '' ?>>
                        <div slot="headline"><?= $i ?></div>
                    </md-select-option>
                <?php endfor; ?>
            </md-outlined-select>

            <md-filled-button type="submit">Buchen</md-filled-button>
        </form>
    </div>

    <?php if ($user_data): ?>
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 8px;">Hallo, <?= htmlspecialchars($user_data['firstname']) ?>!</h2>
        <p style="text-align: center; margin-top: 0; color: var(--md-sys-color-on-surface-variant);">Dein aktuelles Guthaben:</p>
        <div class="balance <?= $user_data['balance'] < 0 ? 'negative' : '' ?>">
            <?= number_format($user_data['balance'], 2, ',', '.') ?> €
        </div>
    </div>
    
    <div class="card">
        <h2>Letzte Aktivitäten</h2>
        <div class="activity-grid">
            <div class="info-item">
                <h3>Letzte Buchung</h3>
                <p>
                    <?php if (isset($user_data['bookings'][0])): ?>
                        <strong><?= $user_data['bookings'][0]['quantity'] ?> Kaffee</strong><br>
                        <small><?= date('d.m.Y H:i', strtotime($user_data['bookings'][0]['booking_time'])) ?></small>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
            <div class="info-item">
                <h3>Vorletzte Buchung</h3>
                <p>
                    <?php if (isset($user_data['bookings'][1])): ?>
                        <strong><?= $user_data['bookings'][1]['quantity'] ?> Kaffee</strong><br>
                        <small><?= date('d.m.Y H:i', strtotime($user_data['bookings'][1]['booking_time'])) ?></small>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
            <div class="info-item">
                <h3>Letzte Einzahlung</h3>
                <p>
                    <?php if (isset($user_data['last_deposit'])): ?>
                        <strong><?= number_format($user_data['last_deposit']['amount'], 2, ',', '.') ?> €</strong><br>
                        <small><?= date('d.m.Y H:i', strtotime($user_data['last_deposit']['transaction_time'])) ?></small>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
             <div class="info-item">
                <h3>Gesamtanzahl Kaffee</h3>
                <?php
                    $stmt_total = $pdo->prepare("SELECT SUM(quantity) as total FROM bookings WHERE user_id = ?");
                    $stmt_total->execute([$user_data['id']]);
                    $total_coffees = $stmt_total->fetchColumn();
                ?>
                <p><strong><?= $total_coffees ?: 0 ?></strong></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

</body>
</html>
