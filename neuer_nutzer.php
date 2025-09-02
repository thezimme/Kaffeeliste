<?php
session_start();

// Wenn keine Daten für einen neuen Nutzer vorhanden sind, zurück zur Startseite
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
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main>
    <div class="card">
        <h1>Unbekannter Nutzer</h1>
        <p style="text-align: center; font-size: 18px; margin-bottom: 24px;">
            Der Nutzer <strong><?= htmlspecialchars($new_user_data['firstname'] . ' ' . $new_user_data['lastname']) ?></strong> wurde nicht gefunden.
        </p>
        <p style="text-align: center;">Möchtest du diesen Nutzer neu anlegen und die Buchung durchführen?</p>

        <form action="user_erstellen.php" method="post" style="margin-top: 20px;">
            <button type="submit" class="button">Ja, anlegen und buchen</button>
        </form>
        <a href="index.php" style="text-decoration: none; margin-top: 12px; display: block;">
            <button type="button" class="button" style="background-color: var(--surface-variant); color: var(--on-surface-variant);">Abbrechen</button>
        </a>
    </div>
</main>

</body>
</html>
