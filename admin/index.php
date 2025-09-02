<?php
session_start();

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

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">

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
<main style="max-width: 400px; margin-top: 50px;">
    <div class="card">
        <h1>🔒 Admin-Bereich</h1>
        <?php
        if (isset($_SESSION['login_error'])) {
            echo '<div class="message error">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
            unset($_SESSION['login_error']);
        }
        ?>
        <form action="login.php" method="post" style="display: flex; flex-direction: column; gap: 20px; margin-top: 24px;">
            <md-outlined-text-field type="password" label="Passwort" name="password" required></md-outlined-text-field>
            <md-filled-button type="submit">Anmelden</md-filled-button>
        </form>
    </div>
</main>
</body>
</html>
