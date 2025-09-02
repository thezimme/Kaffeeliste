<?php
// admin/edit_transaction.php
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

$stmt = $pdo->prepare("
    SELECT t.*, u.firstname, u.lastname 
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = ?
");
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einzahlung bearbeiten</title>
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
    <div class="card">
        <a href="user_details.php?id=<?= $transaction['user_id'] ?>" style="text-decoration: none; align-self: flex-start; margin-bottom: 16px;">
            <md-text-button>
                <span class="material-symbols-outlined" slot="icon">arrow_back</span>
                Zurück zu <?= htmlspecialchars($transaction['firstname']) ?>
            </md-text-button>
        </a>
        <h1 style="margin-top:0;">Einzahlung bearbeiten</h1>
         <p style="margin-top:-16px; margin-bottom: 24px; color: var(--md-sys-color-on-surface-variant);">
            Einzahlung vom <?= date('d.m.Y H:i', strtotime($transaction['transaction_time'])) ?>
        </p>
        <form action="update_transaction.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
            <input type="hidden" name="user_id" value="<?= $transaction['user_id'] ?>">
            <input type="hidden" name="old_amount" value="<?= $transaction['amount'] ?>">
            
            <md-outlined-text-field label="Betrag in €" type="number" step="0.01" name="amount" required value="<?= $transaction['amount'] ?>"></md-outlined-text-field>
            <md-outlined-text-field label="Beschreibung" name="description" required value="<?= htmlspecialchars($transaction['description']) ?>"></md-outlined-text-field>
            
            <md-filled-button type="submit">
                <span class="material-symbols-outlined" slot="icon">save</span>
                Änderungen speichern
            </md-filled-button>
        </form>
    </div>
</main>
</body>
</html>
