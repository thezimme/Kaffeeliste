<?php
// user_erstellen.php
require_once 'config.php';
session_start();

// Prüfen, ob Daten für neuen Nutzer in der Session sind
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['new_user_data'])) {
    header('Location: index.php');
    exit();
}

$new_user_data = $_SESSION['new_user_data'];
$firstname = $new_user_data['firstname'];
$lastname = $new_user_data['lastname'];
$reference = $new_user_data['reference'];
$quantity = $new_user_data['quantity'];
$total_cost = KAFFEE_PREIS * $quantity;

// Transaktion starten
$pdo->beginTransaction();
try {
    // 1. Neuen Nutzer in 'users' anlegen (Guthaben startet bei 0 und wird dann verringert)
    // Die OE (`reference`) wird jetzt mitgespeichert!
    $stmt_create = $pdo->prepare("INSERT INTO users (firstname, lastname, oe, balance) VALUES (?, ?, ?, ?)");
    $stmt_create->execute([$firstname, $lastname, $reference, -$total_cost]);
    $user_id = $pdo->lastInsertId(); // Die ID des gerade erstellten Nutzers holen

    // 2. Die erste Buchung eintragen
    $stmt_booking = $pdo->prepare("INSERT INTO bookings (user_id, quantity, reference) VALUES (?, ?, ?)");
    $stmt_booking->execute([$user_id, $quantity, $reference]);

    // Transaktion bestätigen
    $pdo->commit();

    $_SESSION['message'] = 'Nutzer ' . htmlspecialchars($firstname) . ' wurde angelegt und der Kaffee gebucht!';
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = 'Fehler beim Anlegen des Nutzers: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

// Session-Daten für neuen Nutzer löschen
unset($_SESSION['new_user_data']);

// Cookie setzen
$cookie_data = json_encode(['firstname' => $firstname, 'lastname' => $lastname, 'reference' => $reference]);
setcookie('coffee_user', $cookie_data, time() + (86400 * 30), "/");

// Zurück zur Startseite
header('Location: index.php');
exit();
?>
