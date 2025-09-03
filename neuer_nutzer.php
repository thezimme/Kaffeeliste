<?php
require_once 'security_check.php'; // Stellt sicher, dass der Zugriff legitim ist

if (!isset($_SESSION['new_user_data'])) {
    header('Location: index.php');
    exit();
}
$new_user_data = $_SESSION['new_user_data'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neuen Nutzer anlegen?</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="style.css">

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
    <div class="card" style="text-align: center;">
        <h1>
            <span class="material-symbols-outlined">person_search</span>
            Unbekannter Nutzer
        </h1>
        <p style="font-size: 1.1em; margin-bottom: 24px; line-height: 1.6;">
            Der Nutzer <strong><?= htmlspecialchars($new_user_data['firstname'] . ' ' . $new_user_data['lastname']) ?></strong> mit der OE <strong><?= htmlspecialchars($new_user_data['reference']) ?></strong> wurde nicht gefunden.
        </p>
        <p>Möchtest du diesen Nutzer neu anlegen und die Buchung durchführen?</p>

        <form action="user_erstellen.php" method="post" style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
            <md-filled-button type="submit">
                <span class="material-symbols-outlined" slot="icon">person_add</span>
                Ja, anlegen und buchen
            </md-filled-button>
            <a href="index.php" style="text-decoration: none;">
                <md-outlined-button style="width: 100%;">
                    <span class="material-symbols-outlined" slot="icon">cancel</span>
                    Abbrechen
                </md-outlined-button>
            </a>
        </form>
    </div>
</main>
</body>
</html>
