<?php
session_start();
include("../connexion.inc.php");

date_default_timezone_set('Europe/Amsterdam');
$user_id = $_SESSION['u_id'];

$mois_fr = [
  1 => 'Janvier', 
  2 => 'Février', 
  3 => 'Mars', 
  4 => 'Avril', 
  5 => 'Mai', 
  6 => 'Juin',
  7 => 'Juillet', 
  8 => 'Août', 
  9 => 'Septembre', 
  10 => 'Octobre', 
  11 => 'Novembre', 
  12 => 'Décembre'
];

$today = new DateTime();
$annee = $today->format('Y');
$jour = $today->format('j');
$mois = $mois_fr[(int)$today->format('n')];
$date = $today->format('Y-m-j');

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

?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Accueil</title>
    <link rel="stylesheet" href="header.css" />
    <link rel="stylesheet" href="accueil.css" />
    <link rel="stylesheet" href="../color.css">
  </head>
  <body>
    <header>
      <div class="seniorchat">
        <img src="../imgs/logo.png" />
      </div>
      <div class="links">
        <a href="../agenda/index.php"><img src="../imgs/agenda.png" /></a>
        <a href="../messagerie/chat.php"><img src="../imgs/messages.png" /></a>
        <a href="../profil/profil.php"><img src="../imgs/profil.png" /></a>
        <form action="" method="post">
          <input type="submit" name="logout" value="">
        </form>
        
      </div>
    </header>

    <div class="center-page">
      <div class="calendar">
        <div class="month">
          <?php
          
            echo '<h1 class="month-year-title">' . $mois . ' ' . $annee . '</h1>';
            echo '<h2 class="interval-day">Vos activités du jour (' . $jour . ' ' . $mois . ')</h2>';

            // on selectionne les activités du jours où il est inscrit
            $qry = $cnx -> prepare("SELECT * FROM s_inscrire s JOIN evenement e ON s.id_e = e.id_e WHERE id=? AND date_h=?");
            $qry->execute([$user_id, $date]);
            $eventsOfTheDay = $qry->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <div class="event-day">
            <?php if (count($eventsOfTheDay) > 0): ?>
              <?php foreach($eventsOfTheDay as $event):?>
                
                  <div class="event-day-right">
                    <p><?=$event['libelle']?></p>
                    <p>Heure: <?= date('H:i', strtotime($event['heure'] ?? '')) ?></p>
                  </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>Vous n'avez rien de prévu aujourd'hui.</p>
            <?php endif; ?>
          </div>
      </div>
    </div>
  </body>
  <script>
    function setActive(id) {
      var event = document.getElementById(id);
      if (event.classList.contains("day-right-active")) {
        event.classList.remove("day-right-active");
      } else {
        event.classList.add("day-right-active");
      }
    }
    function showPopup(id, idPopup) {
      var popup = document.getElementById(idPopup);
      var event = document.getElementById(id);
      popup.style.top =
        event.getBoundingClientRect().top + event.clientHeight / 2 + "px";
      popup.style.left =
        event.getBoundingClientRect().left + event.clientWidth + 20 + "px";

      if (popup.style.display === "block") {
        popup.style.display = "none";
      } else {
        popup.style.display = "block";
      }
    }

    function showAddEvent(isVisible) {
      var event = document.getElementById("add-event");
      if (isVisible) {
        event.style.display = "none";
      } else {
        event.style.display = "block";
      }
    }
  </script>
</html>
