<?php
include("../connexion.inc.php");
session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === '+') {
    if (isset($_POST['id_e'])) {
        try {
            // Démarrer une transaction
            $cnx->beginTransaction();

            // Sécurisation de l'input
            $id_e = (int)$_POST['id_e'];
            $user_id = $_SESSION['u_id'];

            // 1. Vérifier que l'événement existe et a des places disponibles
            $event = $cnx->prepare("SELECT nbplaces FROM evenement WHERE id_e = ? FOR UPDATE");
            $event->execute([$id_e]);
            $event_data = $event->fetch();

            if (!$event_data) {
                $error = "Événement introuvable";
                throw new Exception("Événement introuvable");
            }

            if ($event_data['nbplaces'] <= 0) {
                $error = "Plus de places disponibles";
                throw new Exception("Plus de places disponibles");
            }

            // 2. Vérifier que l'utilisateur n'est pas déjà inscrit
            $check = $cnx->prepare("SELECT 1 FROM s_inscrire WHERE id = ? AND id_e = ?");
            $check->execute([$user_id, $id_e]);

            if ($check->fetch()) {
                $error = "Vous êtes déjà inscrit à cet événement";
                throw new Exception("Vous êtes déjà inscrit à cet événement");
            }

            // 3. Décrémenter le nombre de places
            $update = $cnx->prepare("UPDATE evenement SET nbplaces = nbplaces - 1 WHERE id_e = ?");
            $update->execute([$id_e]);

            // 4. Ajouter l'inscription
            $inscription = $cnx->prepare("INSERT INTO s_inscrire (id, id_e) VALUES (?, ?)");
            $inscription->execute([$user_id, $id_e]);

            // Valider la transaction
            $cnx->commit();

            // Stocker le message de succès en session
            $_SESSION['flash_message'] = "Inscription réussie !";
            $_SESSION['flash_type'] = "success";

        } catch (Exception $e) {
            // Annuler en cas d'erreur
            $cnx->rollBack();
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = "error";
        }

        // Rediriger vers la même page
        header("Location: ".$_SERVER['REQUEST_URI']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="color-font.css">
    
<title>Agenda</title>
</head>

<body>
    <?php 
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
                <!-- php -->
                <?php
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
                        echo '<div class="conf">';
                        // convertit la date dans le bon format pour pouvoir l'afficher comme on veut
                        $date = new DateTime($event['date_h']);
                        $jour = $date->format('j');
                        $mois = $mois_fr[(int)$date->format('n')];
                        
                        // affiche le jour et le mois
                        echo "<h1>$jour<br>$mois</h1>";
                        
                        // affiche le libelle de l'event
                        echo '<p>' . htmlspecialchars($event['libelle']) . '</p>';
                        
                        //affiche le nombre de places
                        echo '<p>' . htmlspecialchars($event['nbplaces']) . ' places</p>';

                        // bouton pour s'inscrire à l'event
                        echo '  <form action="" method="post">
                                    <input type="hidden" name="id_e" value="' . htmlspecialchars($event['id_e']) . '">
                                    <input type="submit" name="action" value="+">
                                </form>';
                        echo '</div>';
                    }  
                ?>
            </div>
        </div>

        <div class="middle">    
            <div class="top">
                <div class="title">
                    <button onclick="decreaseMonth()"><img class="left" src="../img/fleche2.png" alt=""></button>
                    <h1 id="month"></h1><h1 id="year"></h1>
                    <button onclick="increaseMonth()"><img src="../img/fleche2.png" alt=""></button>
                </div>
                <div class="today">
                    <button onclick="today()">Aujourd'hui</button>
                </div>
            </div>
            <div class="bottom">
                <div class="calendar">
                    <div class="week">
                        <div class="day">
                            <h1>Lun</h1>
                        </div>
                        <div class="day">
                            <h1>Mar</h1>
                        </div>
                        <div class="day">
                            <h1>Mer</h1>
                        </div>
                        <div class="day">
                            <h1>Jeu</h1>
                        </div>
                        <div class="day">
                            <h1>Ven</h1>
                        </div>
                        <div class="day">
                            <h1>Sam</h1>
                        </div>
                        <div class="day">
                            <h1>Dim</h1>
                        </div>
                    </div>
                    <div class="days" id="days">
                        
                    </div>
                </div>
                <div class="add-event">
                    <button onclick="popup_open()">+</button>
                </div>
            </div>
        </div>
        <div class="pop-up" id="popup">    
            <div class="close">
                <button onclick="popup_close()">
                    <img src="/icon/button-icon/croix (2).png" alt="">
                </button>
            </div>
            <div class="title">
                <h1>Ajouter une conference</h1>
            </div>
            <div class="inputs">
                <form action="" method="post">
                    <input type="text" placeholder="Titre" name="titre">
                    <input type="text" placeholder="Thème" name="theme">
                    <input type="text" placeholder="Type d'intervention" name="type">
                    <input type="text" placeholder="Langue" name="langue">
                    <div class="lieu">
                        <input type="text" placeholder="Salle" name="salle">
                        <input type="text" placeholder="Aile" name="aile">
                    </div>
                    <div class="duree">
                        <input type="text" placeholder="Debut" name="debut">
                        <img src="/icon/fleche.png" alt="fleche">
                        <input type="text" placeholder="Fin" name="fin">
                    </div>
                    <textarea name="Description" value="Description" placeholder="Description" ></textarea>
                    <div class="buttons">
                        <input type="reset" value="Annuler">
                        <input type="submit" value="Confirmer">
                    </div>
                </form>
            </div>
        </div>
        <div class="right">
            <div class="top">
                <h1>jeu. 5</h1>
            </div>
            
            <div class="evenements">
                <!-- php -->
                <div class="evenement">
                    <div class="top">
                        <h2>La santé en voyage</h2>
                        <button onclick="afficheDescription('bottom1','fleche1')"><img id="fleche1" src="../img/fleche.png" alt=""></button>
                    </div>
                    <div class="bottom" id="bottom1">
                        <hr>
                        <div class="description">
                            <p class="duree">15:00-16h30</p>
                            <p class="salle">Salle 255 - Aile B</p>
                        </div>
                        <form action="" method="post">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                    
                </div>
                <!-------->

                <div class="evenement">
                    <div class="top">
                        <h2>La santé en voyage</h2>
                        <button onclick="afficheDescription('bottom2','fleche2')"><img id="fleche2" src="../img/fleche.png" alt=""></button>
                    </div>
                    <div class="bottom" id="bottom2">
                        <hr>
                        <div class="description">
                            <p class="duree">15:00-16h30</p>
                            <p class="salle">Salle 255 - Aile B</p>
                        </div>
                        <form action="" method="post">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                </div>
                <div class="evenement">
                    <div class="top">
                        <h2>La santé en voyage</h2>
                        <button onclick="afficheDescription('bottom3','fleche3')"><img id="fleche3" src="../img/fleche.png" alt=""></button>
                    </div>
                    <div class="bottom" id="bottom3">
                        <hr>
                        <div class="description">
                            <p class="duree">15:00-16h30</p>
                            <p class="salle">Salle 255 - Aile B</p>
                        </div>
                        <form action="" method="post">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                </div>
                <div class="evenement">
                    <div class="top">
                        <h2>La santé en voyage</h2>
                        <button onclick="afficheDescription('bottom4','fleche4')"><img id="fleche4" src="../img/fleche.png" alt=""></button>
                    </div>
                    <div class="bottom" id="bottom4">
                        <hr>
                        <div class="description">
                            <p class="duree">15:00-16h30</p>
                            <p class="salle">Salle 255 - Aile B</p>
                        </div>
                        <form action="" method="post">
                            <input type="submit" value="Supprimer">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
<script src="js/createCalendar.js"></script>
<script src="js/afficheDescription.js"></script>
<script src="js/daySelected.js"></script>
<script src="js/changeMonth.js"></script>
<script src="js/popup.js"></script>

</html>