<?php
// index.php Neu
require_once 'config.php';
session_start();

// Logik zum Abrufen von Benutzerdaten, falls ein Cookie gesetzt ist
$user_data = null;
$user_names = []; // Initialisiere das Array, um Fehler zu vermeiden
if (isset($_COOKIE['coffee_user'])) {
    $user_names_from_cookie = json_decode($_COOKIE['coffee_user'], true);
    if ($user_names_from_cookie && isset($user_names_from_cookie['firstname']) && isset($user_names_from_cookie['lastname'])) {
        $user_names = $user_names_from_cookie; // Weise die Werte zu, wenn sie gültig sind
        $stmt = $pdo->prepare("SELECT * FROM users WHERE firstname = ? AND lastname = ?");
        $stmt->execute([$user_names['firstname'], $user_names['lastname']]);
        $user_data = $stmt->fetch();

        if ($user_data) {
            // Hole letzte zwei Buchungen
            $stmt_bookings = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_time DESC LIMIT 2");
            $stmt_bookings->execute([$user_data['id']]);
            $user_data['bookings'] = $stmt_bookings->fetchAll();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaffeeliste</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main>
    <div class="card">
        <h1>☕ Kaffeeliste</h1>

        <?php
        // Zeige Erfolgs- oder Fehlermeldungen an
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . $_SESSION['message_type'] . '">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <form action="buchen.php" method="post">
            <div class="form-group">
                <label for="firstname">Vorname</label>
                <input type="text" id="firstname" name="firstname" class="input-field" required
                       value="<?= htmlspecialchars($user_names['firstname'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="lastname">Nachname</label>
                <input type="text" id="lastname" name="lastname" class="input-field" required
                       value="<?= htmlspecialchars($user_names['lastname'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="reference">Referat / Abteilung (optional)</label>
                <input type="text" id="reference" name="reference" class="input-field">
            </div>
            <div class="form-group">
                <label for="quantity">Anzahl Kaffee</label>
                <select id="quantity" name="quantity" class="input-field">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="button">Buchen</button>
        </form>
    </div>

    <?php if ($user_data): ?>
    <div class="card user-info">
        <h2>Hallo, <?= htmlspecialchars($user_data['firstname']) ?>!</h2>
        <p>Dein aktuelles Guthaben:</p>
        <div class="balance <?= $user_data['balance'] < 0 ? 'negative' : '' ?>">
            <?= number_format($user_data['balance'], 2, ',', '.') ?> €
        </div>

        <div class="info-grid">
            <div class="info-item">
                <h3>Gesamtanzahl Kaffee</h3>
                <?php
                    $stmt_total = $pdo->prepare("SELECT SUM(quantity) as total FROM bookings WHERE user_id = ?");
                    $stmt_total->execute([$user_data['id']]);
                    $total_coffees = $stmt_total->fetchColumn();
                ?>
                <p><?= $total_coffees ?: 0 ?></p>
            </div>
            <div class="info-item">
                <h3>Letzte Buchung</h3>
                <p>
                    <?php if (isset($user_data['bookings'][0])): ?>
                        <?= $user_data['bookings'][0]['quantity'] ?> Kaffee am <?= date('d.m.Y H:i', strtotime($user_data['bookings'][0]['booking_time'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
            <div class="info-item">
                <h3>Vorletzte Buchung</h3>
                <p>
                    <?php if (isset($user_data['bookings'][1])): ?>
                        <?= $user_data['bookings'][1]['quantity'] ?> Kaffee am <?= date('d.m.Y H:i', strtotime($user_data['bookings'][1]['booking_time'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

</body>
</html>
