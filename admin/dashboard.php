
<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_error'] = 'Bitte zuerst anmelden.';
    header('Location: index.php');
    exit;
}

$users = $pdo->query("
    SELECT id, firstname, lastname, balance, oe
    FROM users ORDER BY lastname, firstname
")->fetchAll();

try {
    // --- BEREITS VORHANDENE STATISTIKEN ---
    $stats_today_total = $pdo->query("SELECT SUM(quantity) as total FROM bookings WHERE DATE(booking_time) = CURDATE()")->fetchColumn();
    $stats_month = $pdo->query("SELECT SUM(quantity) as total FROM bookings WHERE MONTH(booking_time) = MONTH(CURDATE()) AND YEAR(booking_time) = YEAR(CURDATE())")->fetchColumn();
    $stats_year = $pdo->query("SELECT SUM(quantity) as total FROM bookings WHERE YEAR(booking_time) = YEAR(CURDATE())")->fetchColumn();

    // --- NEUE STATISTIKEN ---
    // "Kaffeekönig" des Tages (ROBUSTERE ABFRAGE)
    $top_drinker_today = $pdo->query("
        SELECT u.firstname, u.lastname, SUM(b.quantity) as total_quantity
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE DATE(b.booking_time) = CURDATE()
        GROUP BY u.id, u.firstname, u.lastname
        ORDER BY total_quantity DESC
        LIMIT 1
    ")->fetch();

    // "Kaffeekönig" insgesamt (ROBUSTERE ABFRAGE)
    $top_drinker_overall = $pdo->query("
        SELECT u.firstname, u.lastname, SUM(b.quantity) as total_quantity
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        GROUP BY u.id, u.firstname, u.lastname
        ORDER BY total_quantity DESC
        LIMIT 1
    ")->fetch();

    // Durchschnittlicher Kaffeeverbrauch pro Tag
    $avg_coffee_per_day = $pdo->query("
        SELECT AVG(daily_total)
        FROM (
            SELECT SUM(quantity) as daily_total
            FROM bookings
            GROUP BY DATE(booking_time)
        ) as daily_sums
    ")->fetchColumn();

    // --- DATEN FÜR DIE GRAFIK ---
    $chart_data_query = $pdo->query("
        SELECT DATE_FORMAT(booking_time, '%Y-%m-%d') as date, SUM(quantity) as total
        FROM bookings
        WHERE booking_time >= CURDATE() - INTERVAL 6 DAY
        GROUP BY DATE_FORMAT(booking_time, '%Y-%m-%d')
        ORDER BY date ASC
    ");
    $chart_data = $chart_data_query->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Fallback, falls eine Statistik-Abfrage fehlschlägt, um einen 500-Error zu verhindern
    $_SESSION['message'] = 'Fehler beim Laden der Statistiken.';
    $_SESSION['message_type'] = 'error';
    $stats_today_total = $stats_month = $stats_year = $avg_coffee_per_day = 0;
    $top_drinker_today = $top_drinker_overall = null;
    $chart_data = [];
}

$labels = [];
$data = [];
if (!empty($chart_data)) {
    $date_template = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d.m.', strtotime($date));
        $date_template[$date] = 0;
    }
    foreach ($chart_data as $row) {
        $date_template[$row['date']] = (int)$row['total'];
    }
    $data = array_values($date_template);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>☕</text></svg>">

    <script type="importmap">
    { "imports": { "@material/web/": "https://esm.run/@material/web/" } }
    </script>
    <script type="module">
      import '@material/web/all.js';
      import {styles as typescaleStyles} from '@material/web/typography/md-typescale-styles.js';
      document.adoptedStyleSheets.push(typescaleStyles.styleSheet);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        md-outlined-select, md-filled-button { width: 100%; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            text-align: center;
        }
        .stat-item {
            background-color: var(--md-sys-color-surface-variant);
            padding: 16px;
            border-radius: 8px;
        }
        .stat-item h3 {
            margin: 0 0 8px 0;
            font-size: 1em;
            color: var(--md-sys-color-on-surface-variant);
            font-weight: 500;
        }
        .stat-item p {
            margin: 0;
            font-size: 2em;
            font-weight: 700;
            color: var(--md-sys-color-primary);
        }
         .stat-item .sub-text {
            font-size: 0.8em;
            color: var(--md-sys-color-on-surface-variant);
        }
    </style>
</head>
<body>
<main>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <h1>
                <span class="material-symbols-outlined">admin_panel_settings</span>
                Dashboard
            </h1>
            <div>
                <a href="export.php" style="text-decoration: none;">
                    <md-outlined-button>
                        <span class="material-symbols-outlined" slot="icon">download</span>
                        Export
                    </md-outlined-button>
                </a>
                <a href="logout.php" style="text-decoration: none;">
                    <md-outlined-button>
                        <span class="material-symbols-outlined" slot="icon">logout</span>
                        Logout
                    </md-outlined-button>
                </a>
            </div>
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
        <h2>Statistiken</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <h3>Heute</h3>
                <p><?= $stats_today_total ?: 0 ?></p>
            </div>
            <div class="stat-item">
                <h3>Dieser Monat</h3>
                <p><?= $stats_month ?: 0 ?></p>
            </div>
            <div class="stat-item">
                <h3>Dieses Jahr</h3>
                <p><?= $stats_year ?: 0 ?></p>
            </div>
        </div>
        <div style="margin-top: 24px;">
            <canvas id="coffeeChart"></canvas>
        </div>
        
        <div class="stats-grid" style="margin-top: 24px;">
            <div class="stat-item">
                <h3>Kaffeekönig (Heute)</h3>
                <?php if ($top_drinker_today): ?>
                    <p><?= $top_drinker_today['total_quantity'] ?></p>
                    <p class="sub-text"><?= htmlspecialchars($top_drinker_today['firstname'] . ' ' . $top_drinker_today['lastname']) ?></p>
                <?php else: ?>
                    <p>-</p>
                    <p class="sub-text">Heute noch nichts gebucht</p>
                <?php endif; ?>
            </div>
            <div class="stat-item">
                <h3>Kaffeekönig (Gesamt)</h3>
                 <?php if ($top_drinker_overall): ?>
                    <p><?= $top_drinker_overall['total_quantity'] ?></p>
                    <p class="sub-text"><?= htmlspecialchars($top_drinker_overall['firstname'] . ' ' . $top_drinker_overall['lastname']) ?></p>
                <?php else: ?>
                    <p>-</p>
                <?php endif; ?>
            </div>
            <div class="stat-item">
                <h3>Ø Kaffee / Tag</h3>
                <p><?= number_format($avg_coffee_per_day, 1, ',', '.') ?></p>
                <p class="sub-text">Durchschnittlicher Verbrauch</p>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Nutzerübersicht</h2>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead><tr><th>Name</th><th>OE</th><th>Guthaben</th><th class="actions">Aktionen</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr style="cursor:pointer;" onclick="window.location.href='user_details.php?id=<?= $user['id'] ?>'">
                        <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                        <td><?= htmlspecialchars($user['oe']) ?></td>
                        <td class="balance <?= $user['balance'] < 0 ? 'negative' : '' ?>"><?= number_format($user['balance'], 2, ',', '.') ?> €</td>
                        <td class="actions">
                            <a href="edit_user.php?id=<?= $user['id'] ?>" onclick="event.stopPropagation()">
                                <md-icon-button>
                                    <span class="material-symbols-outlined">edit</span>
                                </md-icon-button>
                            </a>
                             <a href="user_details.php?id=<?= $user['id'] ?>" onclick="event.stopPropagation()">
                                <md-icon-button>
                                    <span class="material-symbols-outlined">visibility</span>
                                </md-icon-button>
                             </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<script>
    const ctx = document.getElementById('coffeeChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Kaffee pro Tag',
                data: <?= json_encode($data) ?>,
                backgroundColor: 'rgba(0, 99, 156, 0.2)',
                borderColor: 'rgba(0, 99, 156, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>
</body>
</html>
