<?php
// hash_generator.php

// Trage hier dein gewünschtes Admin-Passwort ein
$klartextPasswort = 'KaffeePause123!';

// Erzeuge den sicheren Hash
$hash = password_hash($klartextPasswort, PASSWORD_DEFAULT);

// Zeige den Hash an
echo 'Dein Passwort-Hash lautet:<br><br>';
echo '<textarea rows="4" cols="80">' . $hash . '</textarea>';
?>
