<?php
include("../connexion.inc.php");
session_start();

// Vérification de session et logout
if (!isset($_SESSION['identifiant'])) {
    header("Location: ../login/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php");
    exit;
}

// Gestion des inscriptions aux événements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === '+') {
    if (isset($_POST['id_e'])) {
        try {
            $cnx->beginTransaction();
            $id_e = (int)$_POST['id_e'];
            $user_id = $_SESSION['u_id'];

            // Vérification événement
            $event = $cnx->prepare("SELECT nbplaces FROM evenement WHERE id_e = ? FOR UPDATE");
            $event->execute([$id_e]);
            $event_data = $event->fetch();

            if (!$event_data) {
                throw new Exception("Événement introuvable");
            }

            if ($event_data['nbplaces'] <= 0) {
                throw new Exception("Plus de places disponibles");
            }

            // Vérification inscription existante
            $check = $cnx->prepare("SELECT 1 FROM s_inscrire WHERE id = ? AND id_e = ?");
            $check->execute([$user_id, $id_e]);

            if ($check->fetch()) {
                throw new Exception("Vous êtes déjà inscrit à cet événement");
            }

            // Mise à jour et inscription
            $update = $cnx->prepare("UPDATE evenement SET nbplaces = nbplaces - 1 WHERE id_e = ?");
            $update->execute([$id_e]);

            $inscription = $cnx->prepare("INSERT INTO s_inscrire (id, id_e) VALUES (?, ?)");
            $inscription->execute([$user_id, $id_e]);

            $cnx->commit();
            $_SESSION['flash_message'] = "Inscription réussie !";
            $_SESSION['flash_type'] = "success";

        } catch (Exception $e) {
            $cnx->rollBack();
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }

        header("Location: ".$_SERVER['REQUEST_URI']);
        exit;
    }
}

// --- Gestion du calendrier ---

// Initialisation sécurisée des dates
$currentYear = date('Y');
$currentMonth = date('n');

if (isset($_GET['month']) && isset($_GET['year'])) {
    $month = max(1, min(12, (int)$_GET['month']));
    $year = max(2020, min(2100, (int)$_GET['year']));
    $_SESSION['month'] = $month;
    $_SESSION['year'] = $year;
} else {
    $month = $_SESSION['month'] ?? $currentMonth;
    $year = $_SESSION['year'] ?? $currentYear;
    $month = max(1, min(12, $month));
    $year = max(2020, min(2100, $year));
}

// Validation du nombre de jours dans le mois
try {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    if ($daysInMonth === false) {
        throw new Exception("Date invalide");
    }
} catch (Exception $e) {
    // Fallback aux valeurs courantes
    $month = $currentMonth;
    $year = $currentYear;
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    error_log("Erreur calendrier: ".$e->getMessage());
    $_SESSION['flash_message'] = "Problème d'affichage du calendrier - Mois courant affiché";
    $_SESSION['flash_type'] = "error";
}

// // Gestion de la date sélectionnée
$selectedDay = null;
$selectedDate = null;
$dailyEvents = [];

if (isset($_GET['selected_day'])) {
    $selectedDay = (int)$_GET['selected_day'];
    // Validation du jour sélectionné
    if ($selectedDay >= 1 && $selectedDay <= $daysInMonth) {
        $selectedDate = sprintf("%04d-%02d-%02d", $year, $month, $selectedDay);
        
        try {
            $stmt = $cnx->prepare("SELECT * FROM evenement as e JOIN s_inscrire as i ON e.id_e = i.id_e WHERE date_h = ? AND id = ?");
            $stmt->execute([$selectedDate, $_SESSION['u_id']]);
            $dailyEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur BD: ".$e->getMessage());
            $_SESSION['flash_message'] = "Erreur lors de la récupération des événements";
            $_SESSION['flash_type'] = "error";
        }
    } else {
        $_SESSION['flash_message'] = "Jour sélectionné invalide";
        $_SESSION['flash_type'] = "error";
    }
}

// Calcul des jours de la semaine
$firstDayOfWeek = date('w', strtotime("$year-$month-01"));
$firstDayOfWeek = $firstDayOfWeek == 0 ? 7 : $firstDayOfWeek; // Dimanche à 7

// Navigation entre mois
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

// Tableaux pour l'affichage
$weekDays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
$monthNames = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

// gestion de la desinscription des évenements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteEvent']) && $_POST['deleteEvent'] === 'Se désinscrire') {
    if (isset($_POST['id_e'])) {
        try {
            $cnx->beginTransaction();
            $id_e = (int)$_POST['id_e'];
            $user_id = $_SESSION['u_id'];

            // Vérification événement
            $event = $cnx->prepare("SELECT id FROM s_inscrire WHERE id_e = ? AND id = ? FOR UPDATE");
            $event->execute([$id_e, $user_id]);
            $event_data = $event->fetch();

            if (!$event_data) {
                throw new Exception("Événement introuvable");
            }

            // Vérification inscription existante
            $check = $cnx->prepare("SELECT 1 FROM s_inscrire WHERE id = ? AND id_e = ?");
            $check->execute([$user_id, $id_e]);

            if (!$check->fetch()) {
                throw new Exception("L'évenement n'existe pas");
            }

            // suppression de l'entrée dans la table s_inscrire
            $delete = $cnx->prepare("DELETE FROM s_inscrire WHERE id_e = ? AND id = ?");
            $delete->execute([$id_e, $user_id]);
            
            // si l'event est perso alors on le supprime de la table evenement.
            // on verif d'abord si l'event est perso
            $verif = $cnx->prepare("SELECT * FROM evenement WHERE id_e = ?");
            $verif->execute([$id_e]);
            $rq = $verif->fetch();

            // ensuite on le supprime
            if ($rq['perso'] == TRUE) {    
                $delete2 = $cnx->prepare("DELETE FROM evenement WHERE id_e = ?");
                $delete2->execute([$id_e]);
            }

            // Mise à jour du nb de places
            $update = $cnx->prepare("UPDATE evenement SET nbplaces = nbplaces + 1 WHERE id_e = ?");
            $update->execute([$id_e]);

            $cnx->commit();
            $_SESSION['flash_message'] = "Désinscription réussie !";
            $_SESSION['flash_type'] = "success";

        } catch (Exception $e) {
            $cnx->rollBack();
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }

        header("Location: ".$_SERVER['REQUEST_URI']);
        exit;
    }
}

// gestion des ajouts d'évenement personnels.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addEvent'])) {
        $message = "ajout d'evenement";
        try {
            $cnx->beginTransaction();

            $user_id = $_SESSION['u_id'];
            $libelle = $_POST['titre'];
            $date = $_POST['date'];
            $heure = $_POST['heure'];

            // empeche d'avoir des champs vides
            if (empty($libelle) || empty($date) || empty($heure)) {
                throw new Exception("Tous les champs doivent être remplis.");
            }

            // on cherche l'id_e le plus grand et on ajoute 1 pour obtenir l'id_e du nouvel évenement.
            $event = $cnx->prepare("SELECT MAX(id_e) as max_id FROM evenement");
            $event->execute();
            $event_data = $event->fetch(PDO::FETCH_ASSOC);
            $event_id = ($event_data['max_id'] ?? 100) + 1;

            // Vérification si l'évenement n'existe pas déjà ou si il n'y a pas déjà un évenement à au même moment.
            $check = $cnx->prepare("
                SELECT e.id_e 
                FROM evenement e 
                JOIN s_inscrire s ON e.id_e = s.id_e 
                WHERE e.date_h = ? AND e.heure = ? AND s.id = ?
            ");
            $check->execute([$date, $heure, $user_id]);
            if ($check->fetch()) {
                throw new Exception("Vous avez déjà un événement personnel à ce moment.");
            }
            
            // insertion de l'évenement dans la table evenement.
            $event = $cnx->prepare("INSERT INTO evenement VALUES (?, ?, ?, ?, ?, ?)");
            $event->execute([$event_id, $libelle, $date, 0, $heure, TRUE]);
    
            // liaison de l'évenement au sénior avec la table s_inscrire.
            $inscription = $cnx->prepare("INSERT INTO s_inscrire (id, id_e) VALUES (?, ?)");
            $inscription->execute([$user_id, $event_id]);
            
            $cnx->commit();
            $_SESSION['flash_message'] = "Ajout réussie !";
            $_SESSION['flash_type'] = "success";
    
        } catch (Exception $e) {
            $cnx->rollBack();
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }
    
        header("Location: ".$_SERVER['REQUEST_URI']);
        exit;
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="/color.css">
    
<title>Agenda</title>
</head>

<body>
    <?php if (isset($e)): ?>
        <div class="error-date">
            Problème d'affichage du calendrier - Affichage du mois courant
        </div>
    <?php endif; ?>
    
    <?php // affiche un popup  pour indiquer plein de trucs comme l'inscription.
        if(isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            echo "<script>alert('".$message."');</script>";
            unset($_SESSION['flash_message']);
        }
    ?>
    <div class="container">
        <div class="left">
            <div class="top">
                <form id="logout" method="POST">
                    <input type="submit" name="logout" value="">
                </form>
                <h1>evenements <br> à venir</h1>
            </div>
            
            <div class="confs">
                
                <?php
                // affichage des évenements publics
                    $mois_fr = [
                        1 => 'JANV', 
                        2 => 'FÉVR', 
                        3 => 'MARS', 
                        4 => 'AVR', 
                        5 => 'MAI', 
                        6 => 'JUIN',
                        7 => 'JUIL', 
                        8 => 'AOÛT', 
                        9 => 'SEPT', 
                        10 => 'OCT', 
                        11 => 'NOV', 
                        12 => 'DÉC'
                    ];
                    $query = $cnx->query("SELECT * FROM evenement ORDER BY date_h;");
                    $events = $query->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($events as $event) {
                        if (!$event['perso']) {
                            echo '<div class="conf">';
                            // convertit la date dans le bon format pour pouvoir l'afficher comme on veut
                            $date = new DateTime($event['date_h']);
                            $jour = $date->format('j');
                            $mois = $mois_fr[(int)$date->format('n')];
                            
                            // affiche le jour et le mois
                            echo "<h1>$jour<br>$mois</h1>";
                            
                            echo '<div class="description">';

                            // affiche le libelle de l'event
                            echo '<p>' . $event['libelle'] . '</p>';
                            echo '<p>Heure: ' . date('H:i', strtotime($event['heure'] ?? '')) . '</p>';
                            //affiche le nombre de places
                            echo '<p>' . $event['nbplaces'] . ' places</p>';
                            echo "</div>";
                            // bouton pour s'inscrire à l'event
                            echo '  <form action="" method="post">
                                        <input type="hidden" name="id_e" value="' . $event['id_e'] . '">
                                        <input type="submit" name="action" class="inscription" value="+">
                                    </form>';
                            echo '</div>';
                        }
                    }  
                ?>
            </div>
        </div>
        
        
        <div class="middle">
            <div class="calendar" id="calendar">
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
                <div class="add-event">
                    <button onclick="popup_open()">Ajouter un événement</button>
                </div>
            </div>
            

            <div class="pop-up" id="popup">
                <div class="close">
                    <button onclick="popup_close()">
                        <img src="../icon/button-icon/croix (2).png" alt="">
                    </button>
                </div>
                <div class="title">
                    <h1>Ajouter une évenement</h1>
                </div>
                <div class="inputs">
                    <form action="" method="POST">
                        <input type="text" placeholder="Titre" name="titre">
                        <input type="date" name="date">
                        <input type="time" step="60" name="heure">
                        <div class="buttons">
                            <input type="reset" value="Annuler">
                            <input type="submit" name="addEvent" value="Confirmer">
                        </div>
                    </form>
                </div>
            </div>
                 
        </div>
        <div class="right">
            <?php if (isset($selectedDate)): ?>
            <div class="top">
                <h1><?= $selectedDay ?> <?= $monthNames[$month] ?></h1>
            </div>
            
            <div class="evenements">
                <?php if (count($dailyEvents) > 0): ?>
                    <?php foreach ($dailyEvents as $event): ?>
                        <div class="evenement">
                            <div class="top">
                                <h2><?= $event['libelle'] ?></h2>
                            </div>
                            <hr>
                            <div class="description">
                                <p>Heure: <?= date('H:i', strtotime($event['heure'] ?? '')) ?></p>
                            </div>
                            <form action="" method="post">
                                <input type="hidden" name="id_e" value="<?= $event['id_e'] ?>">
                                <input type="submit" class="deleteEvent" name="deleteEvent" value="Se désinscrire">
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun événement prévu ce jour.</p>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <div class="select-day">
                    <p>Cliquez sur un jour pour voir les événements</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
<script src="js/createCalendar.js"></script>
<script src="js/afficheDescription.js"></script>
<script src="js/daySelected.js"></script>
<script src="js/changeMonth.js"></script>
<script src="js/popup.js"></script>

</html>