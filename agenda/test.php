<?php
// --- Gestion des dates ---
session_start();
include("../connexion.inc.php");

// Si on change de mois via GET, on met à jour la session
if (isset($_GET['month']) && isset($_GET['year'])) {
    $_SESSION['month'] = (int)$_GET['month'];
    $_SESSION['year'] = (int)$_GET['year'];
} else {
    // Sinon on initialise à la date courante
    if (!isset($_SESSION['month']) || !isset($_SESSION['year'])) {
        $_SESSION['month'] = date('n');  // mois (1-12)
        $_SESSION['year'] = date('Y');   // année (4 chiffres)
    }
}

$month = $_SESSION['month'];
$year = $_SESSION['year'];

// Gestion de la date sélectionnée
$selectedDay = null;
$selectedDate = null;
$events = [];

if (isset($_GET['selected_day'])) {
    $selectedDay = (int)$_GET['selected_day'];
    $selectedDate = sprintf("%04d-%02d-%02d", $year, $month, $selectedDay);
    
    // Requête pour les événements
    try {
        $stmt = $cnx->prepare("SELECT * FROM evenement WHERE date_h = ?");
        $stmt->execute([$selectedDate]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération des événements";
    }
}

// Nombre de jours dans le mois
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Jour de la semaine du 1er jour du mois (0 = dimanche, 1 = lundi, etc.)
$firstDayOfWeek = date('w', strtotime("$year-$month-01"));

// On décale dimanche (0) à la fin de la semaine (7)
if ($firstDayOfWeek == 0) {
    $firstDayOfWeek = 7;
}

// Mois précédent et suivant pour navigation
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Nom des jours de la semaine (lundi -> dimanche)
$weekDays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

// Nom du mois pour affichage
$monthNames = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Agenda PHP</title>
<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 20px auto;
        padding: 0 15px;
    }
    h1 {
        text-align: center;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        user-select: none;
    }
    th, td {
        border: 1px solid #ddd;
        width: 14.28%; /* 7 jours */
        height: 80px;
        text-align: center;
        vertical-align: top;
        cursor: pointer;
        position: relative;
    }
    th {
        background-color: #f4f4f4;
        cursor: default;
    }
    td.inactive {
        color: #bbb;
        cursor: default;
        background: #fafafa;
    }
    td .day-number {
        display: block;
        margin-bottom: 20px;
        font-weight: bold;
    }
    td.selected {
        background-color: #e6f7ff;
    }
    .nav {
        margin: 10px 0;
        text-align: center;
    }
    .nav a {
        text-decoration: none;
        color: #333;
        font-weight: bold;
        margin: 0 15px;
        font-size: 18px;
    }
    #events-container {
        margin-top: 30px;
        border-top: 1px solid #ddd;
        padding-top: 20px;
    }
    .event {
        background: #f8f9fa;
        border-left: 4px solid #4CAF50;
        padding: 10px;
        margin: 10px 0;
        border-radius: 0 4px 4px 0;
    }
    .event h3 {
        margin-top: 0;
        color: #333;
    }
    .error {
        color: #f44336;
        padding: 10px;
        background: #ffebee;
        border-left: 4px solid #f44336;
    }
</style>
</head>
<body>

<h1>Agenda - <?= $monthNames[$month] . " " . $year ?></h1>

<div class="nav">
    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?><?= $selectedDay ? '&selected_day='.$selectedDay : '' ?>">&laquo; Mois précédent</a>
    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?><?= $selectedDay ? '&selected_day='.$selectedDay : '' ?>">Mois suivant &raquo;</a>
</div>

<table>
    <thead>
        <tr>
            <?php foreach ($weekDays as $dayName): ?>
                <th><?= $dayName ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $day = 1;
        // 6 semaines pour couvrir tous les cas
        for ($week = 0; $week < 6; $week++):
            echo "<tr>";
            for ($dow = 1; $dow <= 7; $dow++) {
                // Calcul si on est avant le début du mois ou après la fin
                if (($week === 0 && $dow < $firstDayOfWeek) || $day > $daysInMonth) {
                    // Cellule vide ou inactive
                    echo '<td class="inactive"></td>';
                } else {
                    $selectedClass = ($day === $selectedDay) ? 'selected' : '';
                    echo '<td class="'.$selectedClass.'" onclick="window.location.href=\'?month='.$month.'&year='.$year.'&selected_day='.$day.'\'">';
                    echo '<span class="day-number">'.$day.'</span>';
                    echo '</td>';
                    $day++;
                }
            }
            echo "</tr>";
            if ($day > $daysInMonth) break;
        endfor;
        ?>
    </tbody>
</table>

<div id="events-container">
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (isset($selectedDate)): ?>
        <h2>Événements du <?= $selectedDay ?> <?= $monthNames[$month] ?> <?= $year ?></h2>
        
        <?php if (count($events) > 0): ?>
            <?php foreach ($events as $event): ?>
                <div class="event">
                    <h3><?= htmlspecialchars($event['libelle']) ?></h3>
                    <p><?= htmlspecialchars($event['description'] ?? '') ?></p>
                    <p>Places disponibles: <?= htmlspecialchars($event['nbplaces']) ?></p>
                    <p>Heure: <?= date('H:i', strtotime($event['heure'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun événement prévu ce jour.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>Cliquez sur un jour pour voir les événements</p>
    <?php endif; ?>
</div>

</body>
</html>