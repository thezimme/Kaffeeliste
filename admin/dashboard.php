<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_error'] = 'Bitte zuerst anmelden.';
    header('Location: index.php');
    exit;
}
$users = $pdo->query("
    SELECT u.id, u.firstname, u.lastname, u.balance,
    (SELECT reference FROM bookings WHERE user_id = u.id ORDER BY booking_time DESC LIMIT 1) as last_reference
    FROM users u ORDER BY u.lastname, u.firstname
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
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
    
    <style>
        md-outlined-select, md-filled-button { width: 100%; }
    </style>
</head>
<body>
<main>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>
                <span class="material-symbols-outlined">admin_panel_settings</span>
                Admin Dashboard
            </h1>
            <a href="logout.php" style="text-decoration: none;">
                <md-outlined-button>
                    <span class="material-symbols-outlined" slot="icon">logout</span>
                    Logout
                </md-outlined-button>
            </a>
        </div>
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . $_SESSION['message_type'] . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message'], $_SESSION['message_type']);
        }
        ?>
    </div>

    <div class="card">
        <h2>Guthaben aufladen</h2>
        <form action="guthaben_buchen.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <md-outlined-select label="Nutzer auswählen" name="user_id" required>
                <?php foreach ($users as $user): ?>
                    <md-select-option value="<?= $user['id'] ?>"><div slot="headline"><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></div></md-select-option>
                <?php endforeach; ?>
            </md-outlined-select>
            <md-outlined-text-field label="Betrag in €" type="number" step="0.01" name="amount" required></md-outlined-text-field>
            <md-filled-button type="submit">
                 <span class="material-symbols-outlined" slot="icon">add_card</span>
                Guthaben buchen
            </md-filled-button>
        </form>
    </div>

    <div class="card">
        <h2>Nutzerübersicht</h2>
        <table class="data-table">
            <thead><tr><th>Name</th><th>Referat</th><th>Guthaben</th><th class="actions">Aktionen</th></tr></thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr style="cursor:pointer;" onclick="window.location.href='user_details.php?id=<?= $user['id'] ?>'">
                    <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                    <td><?= htmlspecialchars($user['last_reference']) ?></td>
                    <td class="balance <?= $user['balance'] < 0 ? 'negative' : '' ?>"><?= number_format($user['balance'], 2, ',', '.') ?> €</td>
                    <td class="actions">
                        <md-icon-button href="edit_user.php?id=<?= $user['id'] ?>" onclick="event.stopPropagation()">
                            <span class="material-symbols-outlined">edit</span>
                        </md-icon-button>
                         <md-icon-button href="user_details.php?id=<?= $user['id'] ?>" onclick="event.stopPropagation()">
                            <span class="material-symbols-outlined">visibility</span>
                        </md-icon-button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
