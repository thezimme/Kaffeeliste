<?php
// admin/update_user.php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id = (int)$_POST['user_id'];
$firstname = trim($_POST['firstname']);
$lastname = trim($_POST['lastname']);
$reference = trim($_POST['reference']);

if (empty($firstname) || empty($lastname) || empty($reference)) {
    $_SESSION['message'] = 'Alle Felder müssen ausgefüllt sein.';
    $_SESSION['message_type'] = 'error';
    header('Location: edit_user.php?id=' . $user_id);
    exit;
}

// Validierung: OE-Format prüfen (z.B. "V 12")
if (!preg_match('/^[A-Z]\s\d{1,2}$/', $reference)) {
    $_SESSION['message'] = 'Die OE muss dem Format "B 12" entsprechen.';
    $_SESSION['message_type'] = 'error';
    header('Location: edit_user.php?id=' . $user_id);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET firstname = ?, lastname = ?, oe = ? WHERE id = ?");
    $stmt->execute([$firstname, $lastname, $reference, $user_id]);
    
    $_SESSION['message'] = 'Nutzerdaten erfolgreich aktualisiert.';
    $_SESSION['message_type'] = 'success';

} catch (Exception $e) {
    $_SESSION['message'] = 'Fehler beim Aktualisieren der Nutzerdaten: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
}

header('Location: dashboard.php');
exit;
?>
