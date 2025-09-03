<?php
// security_check.php

// Stellt sicher, dass die Session gestartet ist
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lädt die Konfiguration, um auf den Token zugreifen zu können
require_once 'config.php';

// 1. Prüfen, ob der Nutzer bereits autorisiert ist (via Session)
if (isset($_SESSION['is_authorized']) && $_SESSION['is_authorized'] === true) {
    // Nutzer ist bereits autorisiert, alles in Ordnung.
    return;
}

// 2. Wenn nicht, prüfen, ob ein gültiger Token in der URL übergeben wurde
if (isset($_GET['token']) && hash_equals(ACCESS_TOKEN, $_GET['token'])) {
    // Token ist korrekt, Session-Variable setzen, um den Nutzer für die Zukunft zu autorisieren
    $_SESSION['is_authorized'] = true;
} else {
    // 3. Weder eine autorisierte Session noch ein gültiger Token vorhanden -> Zugriff verweigern
    header('HTTP/1.0 403 Forbidden');
    die('Zugriff verweigert. Bitte verwenden Sie den korrekten Zugangslink.');
}
?>
