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
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<main>
    <div class="card">
         <a href="dashboard.php" style="text-decoration: none; color: var(--primary-color);"><md-text-button>
        <span class="material-symbols-outlined" slot="icon">arrow_back</span>
        Zurück zum Dashboard</md-text-button></a>
        <h1 style="margin-top:16px;">Nutzer bearbeiten</h1>
        <form action="update_user.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            
            <md-outlined-text-field label="Vorname" name="firstname" required value="<?= htmlspecialchars($user['firstname']) ?>"></md-outlined-text-field>
            <md-outlined-text-field label="Nachname" name="lastname" required value="<?= htmlspecialchars($user['lastname']) ?>"></md-outlined-text-field>
            <md-outlined-text-field label="Standard-Referat" name="reference" required value="<?= htmlspecialchars($user['last_reference']) ?>"></md-outlined-text-field>
            <small>Ändert nur den Standardwert für neue Buchungen und das Cookie. Bestehende Buchungen bleiben unberührt.</small>
            
            <md-filled-button type="submit">
                <span class="material-symbols-outlined" slot="icon">save</span>
                Speichern
            </md-filled-button>
        </form>
    </div>
</main>
</body>
</html>
