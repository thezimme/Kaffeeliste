<?php
// admin/index.php
session_start();

// Wenn der Admin bereits eingeloggt ist, direkt zum Dashboard weiterleiten
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<main style="max-width: 400px;">
    <div class="card">
        <h1>🔒 Admin-Bereich</h1>

        <?php
        if (isset($_SESSION['login_error'])) {
            echo '<div class="message error">' . $_SESSION['login_error'] . '</div>';
            unset($_SESSION['login_error']);
        }
        ?>

        <form action="login.php" method="post">
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" class="input-field" required>
            </div>
            <button type="submit" class="button">Anmelden</button>
        </form>
    </div>
</main>

</body>
</html>
