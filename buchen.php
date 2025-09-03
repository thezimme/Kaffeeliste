<?php
// buchen.php
require_once 'security_check.php'; // Ersetzt config.php und session_start() und prüft den Zugriff

// Prüfen, ob das Formular gesendet wurde
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Formulardaten abrufen und bereinigen
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$reference = trim($_POST['reference'] ?? '');
$quantity = (int)($_POST['quantity'] ?? 1);

// Validierung: Namen dürfen nicht leer sein
if (empty($firstname) || empty($lastname)) {
    $_SESSION['message'] = 'Vor- und Nachname dürfen nicht leer sein.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit();
}

// Validierung: OE-Format prüfen (z.B. "V 12")
if (!preg_match('/^[A-Z]\s\d{1,2}$/', $reference)) {
    $_SESSION['message'] = 'Die OE muss dem Format "B 12" entsprechen (Großbuchstabe, Leerzeichen, 1-2 Zahlen).';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit();
}


// 1. Prüfen, ob der Benutzer existiert (jetzt mit Vorname, Nachname UND OE)
$stmt = $pdo->prepare("SELECT id FROM users WHERE firstname = ? AND lastname = ? AND oe = ?");
$stmt->execute([$firstname, $lastname, $reference]);
$user = $stmt->fetch();

// 2. Fall: Benutzer existiert
if ($user) {
    $user_id = $user['id'];
    $total_cost = KAFFEE_PREIS * $quantity;

    // Transaktion starten für Datensicherheit
    $pdo->beginTransaction();
    try {
        // Guthaben des Benutzers aktualisieren
        $stmt_update = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt_update->execute([$total_cost, $user_id]);

        // Buchung in die 'bookings'-Tabelle eintragen
        $stmt_insert = $pdo->prepare("INSERT INTO bookings (user_id, quantity, reference) VALUES (?, ?, ?)");
        $stmt_insert->execute([$user_id, $quantity, $reference]);

        // Transaktion bestätigen
        $pdo->commit();

        $_SESSION['message'] = 'Kaffee erfolgreich gebucht!';
        $_SESSION['message_type'] = 'success';

    } catch (Exception $e) {
        // Bei Fehler alles zurückrollen
        $pdo->rollBack();
        $_SESSION['message'] = 'Ein Fehler ist aufgetreten: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }

} else {
    // 3. Fall: Benutzer existiert nicht -> Nachfragen
    // Daten in der Session speichern für die Bestätigungsseite
    $_SESSION['new_user_data'] = [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'reference' => $reference,
        'quantity' => $quantity
    ];
    header('Location: neuer_nutzer.php');
    exit();
}

// Cookie für 30 Tage setzen, um den Namen und die OE zu speichern
$cookie_data = json_encode(['firstname' => $firstname, 'lastname' => $lastname, 'reference' => $reference]);
setcookie('coffee_user', $cookie_data, time() + (86400 * 30), "/"); // 86400 = 1 Tag

// Zurück zur Startseite leiten
header('Location: index.php');
exit();
?>
