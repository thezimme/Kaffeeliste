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
         <a href="dashboard.php" style="text-decoration: none; color: var(--primary-color);">&larr; Zurück zum Dashboard</a>
        <h1 style="margin-top:16px;">Nutzer bearbeiten</h1>
        <form action="update_user.php" method="POST">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            
            <div class="form-group">
                <label for="firstname">Vorname</label>
                <input type="text" name="firstname" id="firstname" class="input-field" value="<?= htmlspecialchars($user['firstname']) ?>" required>
            </div>
            <div class="form-group">
                <label for="lastname">Nachname</label>
                <input type="text" name="lastname" id="lastname" class="input-field" value="<?= htmlspecialchars($user['lastname']) ?>" required>
            </div>
             <div class="form-group">
                <label for="reference">Standard-Referat</label>
                <input type="text" name="reference" id="reference" class="input-field" value="<?= htmlspecialchars($user['last_reference']) ?>" required>
                 <small>Ändert nur den Standardwert für neue Buchungen und das Cookie. Bestehende Buchungen bleiben unberührt.</small>
            </div>
            <button type="submit" class="button">Speichern</button>
        </form>
    </div>
</main>
</body>
</html>
