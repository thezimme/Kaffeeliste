<?php
// admin/edit_transaction.php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$transaction_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$transaction_id) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einzahlung bearbeiten</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<main>
    <div class="card">
        <h1>Einzahlung bearbeiten</h1>
        <form action="update_transaction.php" method="POST">
            <input type="hidden" name="transaction_id" value="<?= $transaction['id'] ?>">
            <input type="hidden" name="user_id" value="<?= $transaction['user_id'] ?>">
            <input type="hidden" name="old_amount" value="<?= $transaction['amount'] ?>">
            
            <div class="form-group">
                <label for="amount">Betrag in €</label>
                <input type="number" step="0.01" name="amount" id="amount" class="input-field" value="<?= $transaction['amount'] ?>" required>
            </div>
             <div class="form-group">
                <label for="description">Beschreibung</label>
                <input type="text" name="description" id="description" class="input-field" value="<?= htmlspecialchars($transaction['description']) ?>" required>
            </div>
            <button type="submit" class="button">Speichern</button>
        </form>
    </div>
</main>
</body>
</html>
