<?php
// admin/edit_user.php
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

$stmt = $pdo->prepare("
    SELECT 
        u.id, 
        u.firstname, 
        u.lastname,
        (SELECT reference FROM bookings WHERE user_id = u.id ORDER BY booking_time DESC LIMIT 1) as last_reference
    FROM users u 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutzer bearbeiten</title>
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
         <a href="dashboard.php" style="text-decoration: none; align-self: flex-start; margin-bottom: 16px;">
            <md-text-button>
                <span class="material-symbols-outlined" slot="icon">arrow_back</span>
                Zurück zum Dashboard
            </md-text-button>
        </a>
        <h1 style="margin-top:0;">Nutzer bearbeiten</h1>
        <form action="update_user.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            
            <md-outlined-text-field label="Vorname" name="firstname" required value="<?= htmlspecialchars($user['firstname']) ?>"></md-outlined-text-field>
            <md-outlined-text-field label="Nachname" name="lastname" required value="<?= htmlspecialchars($user['lastname']) ?>"></md-outlined-text-field>
            <md-outlined-text-field label="Standard-OE" name="reference" required pattern="[A-Z]\s\d{1,2}" title="Bitte im Format 'B 12' eingeben." value="<?= htmlspecialchars($user['last_reference']) ?>"></md-outlined-text-field>
            <p style="font-size: 0.9em; color: var(--md-sys-color-on-surface-variant); margin: -8px 0 8px 0;">
                Ändert nur den Standardwert für neue Buchungen und das Cookie.
            </p>
            
            <md-filled-button type="submit">
                <span class="material-symbols-outlined" slot="icon">save</span>
                Speichern
            </md-filled-button>
        </form>
    </div>
</main>
</body>
</html>
