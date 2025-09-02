<?php
// admin/export.php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$tables = ['users', 'bookings', 'transactions'];
$sql_string = "";

foreach ($tables as $table) {
    $stmt = $pdo->query("SELECT * FROM {$table}");
    $num_columns = $stmt->columnCount();

    $sql_string .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $create_stmt = $pdo->query("SHOW CREATE TABLE {$table}");
    $create_row = $create_stmt->fetch(PDO::FETCH_ASSOC);
    $sql_string .= $create_row['Create Table'] . ";\n\n";

    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $sql_string .= "INSERT INTO `{$table}` VALUES(";
        for ($j = 0; $j < $num_columns; $j++) {
            $row[$j] = addslashes($row[$j]);
            $row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
            if (isset($row[$j])) {
                $sql_string .= '"' . $row[$j] . '"';
            } else {
                $sql_string .= '""';
            }
            if ($j < ($num_columns - 1)) {
                $sql_string .= ',';
            }
        }
        $sql_string .= ");\n";
    }
    $sql_string .= "\n";
}

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="kaffeeliste-backup-' . date('Y-m-d') . '.sql"');
echo $sql_string;
exit;

?>
