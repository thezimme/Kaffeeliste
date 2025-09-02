<?php
// index.php Final
require_once 'config.php';
session_start();

$user_data = null;
$user_names = [];
if (isset($_COOKIE['coffee_user'])) {
    $user_names_from_cookie = json_decode($_COOKIE['coffee_user'], true);
    if ($user_names_from_cookie && isset($user_names_from_cookie['firstname']) && isset($user_names_from_cookie['lastname']) && isset($user_names_from_cookie['reference'])) {
        $user_names = $user_names_from_cookie;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE firstname = ? AND lastname = ? AND oe = ?");
        $stmt->execute([$user_names['firstname'], $user_names['lastname'], $user_names['reference']]);
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>☕</text></svg>">

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
        md-outlined-select, md-filled-button {
            width: 100%;
        }
        .status-card {
            background-color: var(--md-sys-color-primary-container);
            color: var(--md-sys-color-on-primary-container);
        }
        .status-card .balance {
            color: var(--md-sys-color-primary);
        }
        .status-card .balance.negative {
            color: var(--md-sys-color-error);
        }
    </style>
</head>
<body>

<main>

    <?php if ($user_data): ?>
    <div class="card status-card">
        <h2 style="text-align: center; margin: 0; font-weight: 400;">
            Hallo, <span style="font-weight: 500;"><?= htmlspecialchars($user_data['firstname']) ?></span>!
        </h2>
        <p style="text-align: center; margin-top: 8px; color: var(--md-sys-color-on-primary-container);">Dein aktuelles Guthaben:</p>
        <div class="balance <?= $user_data['balance'] < 0 ? 'negative' : '' ?>">
            <?= number_format($user_data['balance'], 2, ',', '.') ?> €
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <h1>
            <span class="material-symbols-outlined">coffee</span>
            Kaffeeliste
        </h1>

        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . $_SESSION['message_type'] . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message'], $_SESSION['message_type']);
        }
        ?>

        <form action="buchen.php" method="post" style="display: flex; flex-direction: column; gap: 20px;">
            <md-outlined-text-field label="Vorname" name="firstname" required value="<?= htmlspecialchars($user_names['firstname'] ?? '') ?>"></md-outlined-text-field>
            <md-outlined-text-field label="Nachname" name="lastname" required value="<?= htmlspecialchars($user_names['lastname'] ?? '') ?>"></md-outlined-text-field>
            <md-outlined-text-field label="OE" name="reference" required pattern="[A-Z]\s\d{1,2}" title="Bitte im Format 'B 12' eingeben." value="<?= htmlspecialchars($user_names['reference'] ?? '') ?>"></md-outlined-text-field>

            <md-outlined-select label="Anzahl Kaffee" name="quantity">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <md-select-option value="<?= $i ?>" <?= ($i == 1) ? 'selected' : '' ?>>
                        <div slot="headline"><?= $i ?></div>
                    </md-select-option>
                <?php endfor; ?>
            </md-outlined-select>

            <md-filled-button type="submit">
                <span class="material-symbols-outlined" slot="icon">coffee</span>
                Buchen
            </md-filled-button>
        </form>
    </div>

    <div class="card">
        <h2>Guthaben aufladen</h2>
        <p>Lade dein Guthaben ganz einfach mit PayPal auf.</p>
        <a href="https://paypal.de" target="_blank" style="text-decoration: none; margin-top: 16px;">
            <md-filled-button>
                <span class="material-symbols-outlined" slot="icon">payments</span>
                Mit PayPal aufladen
            </md-filled-button>
        </a>
    </div>

    <?php if ($user_data): ?>
    <div class="card">
        <h2>Letzte Aktivitäten</h2>
        <md-list>
            <md-list-item>
                <div slot="headline">Letzte Buchung</div>
                <div slot="supporting-text">
                    <?php if (isset($user_data['bookings'][0])): ?>
                        <?= date('d.m.Y H:i', strtotime($user_data['bookings'][0]['booking_time'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div slot="trailing-supporting-text">
                    <?php if (isset($user_data['bookings'][0])): ?>
                        <strong><?= $user_data['bookings'][0]['quantity'] ?> Kaffee</strong>
                    <?php endif; ?>
                </div>
            </md-list-item>
            <md-divider></md-divider>
            <md-list-item>
                <div slot="headline">Vorletzte Buchung</div>
                <div slot="supporting-text">
                    <?php if (isset($user_data['bookings'][1])): ?>
                        <?= date('d.m.Y H:i', strtotime($user_data['bookings'][1]['booking_time'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div slot="trailing-supporting-text">
                    <?php if (isset($user_data['bookings'][1])): ?>
                        <strong><?= $user_data['bookings'][1]['quantity'] ?> Kaffee</strong>
                    <?php endif; ?>
                </div>
            </md-list-item>
             <md-divider></md-divider>
            <md-list-item>
                <div slot="headline">Letzte Einzahlung</div>
                <div slot="supporting-text">
                     <?php if (isset($user_data['last_deposit'])): ?>
                        <?= date('d.m.Y H:i', strtotime($user_data['last_deposit']['transaction_time'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div slot="trailing-supporting-text">
                   <?php if (isset($user_data['last_deposit'])): ?>
                        <strong><?= number_format($user_data['last_deposit']['amount'], 2, ',', '.') ?> €</strong>
                    <?php endif; ?>
                </div>
            </md-list-item>
        </md-list>
    </div>
    <?php endif; ?>
</main>

</body>
</html>
