
<?php
// admin/dashboard.php
require_once '../config.php';
session_start();

// Sicherheits-Check: Ist der Admin eingeloggt?
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_error'] = 'Bitte zuerst anmelden.';
    header('Location: index.php');
    exit;
}

// Alle Nutzer aus der Datenbank laden und das letzte Referat ermitteln
$users = $pdo->query("
    SELECT 
        u.id, 
        u.firstname, 
        u.lastname, 
        u.balance,
        (SELECT reference FROM bookings WHERE user_id = u.id ORDER BY booking_time DESC LIMIT 1) as last_reference
    FROM users u 
    ORDER BY u.lastname, u.firstname
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .user-table { width: 100%; border-collapse: collapse; }
        .user-table th, .user-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--surface-variant); }
        .user-table th { font-weight: 500; }
        .user-table .user-row { cursor: pointer; transition: background-color 0.2s; }
        .user-table .user-row:hover { background-color: var(--surface-variant); }
        .balance.negative { color: #B3261E; font-weight: bold; }
        .logout-btn { background-color: var(--on-surface-variant); margin-top: 10px; }
        .logout-btn:hover { background-color: var(--on-surface); }
        .actions a { text-decoration: none; }
    </style>
</head>
<body>

<main>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>📊 Admin Dashboard</h1>
            <a href="logout.php"><button class="button logout-btn" style="width: auto; padding: 10px 20px;">Logout</button></a>
        </div>
        
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . $_SESSION['message_type'] . '">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
    </div>

    <div class="card">
        <h2>Guthaben aufladen</h2>
        <form action="guthaben_buchen.php" method="POST">
            <div class="form-group">
                <label for="user_id">Nutzer auswählen</label>
                <select name="user_id" id="user_id" class="input-field" required>
                    <option value="">Bitte wählen...</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>">
                            <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="amount">Betrag in €</label>
                <input type="number" step="0.01" name="amount" id="amount" class="input-field" required placeholder="z.B. 10.00">
            </div>
            <button type="submit" class="button">Guthaben buchen</button>
        </form>
    </div>

    <div class="card">
        <h2>Nutzerübersicht</h2>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Referat</th>
                    <th>Guthaben</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td class="user-row" onclick="window.location.href='user_details.php?id=<?= $user['id'] ?>'"><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                    <td class="user-row" onclick="window.location.href='user_details.php?id=<?= $user['id'] ?>'"><?= htmlspecialchars($user['last_reference']) ?></td>
                    <td class="user-row balance <?= $user['balance'] < 0 ? 'negative' : '' ?>" onclick="window.location.href='user_details.php?id=<?= $user['id'] ?>'">
                        <?= number_format($user['balance'], 2, ',', '.') ?> €
                    </td>
                    <td class="actions">
                        <a href="edit_user.php?id=<?= $user['id'] ?>" title="Nutzer bearbeiten">✏️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
