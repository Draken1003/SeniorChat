<?php 
session_start();
include("../connexion.inc.php");

// Vérification de session et logout
if (!isset($_SESSION['identifiant'])) {
    header("Location: ../login/login.php");
    exit;
}

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

// Gestion suppression d'evenements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (isset($_POST['id_e'])) {
        try {
            $cnx->beginTransaction();
            $id_e = (int)$_POST['id_e'];

            // Vérification événement
            $event = $cnx->prepare("SELECT * FROM evenement WHERE id_e = ? FOR UPDATE");
            $event->execute([$id_e]);
            $event_data = $event->fetch();

            if (!$event_data) {
                throw new Exception("Événement introuvable");
            }

            // Mise à jour des evenements
            $deleteContenir = $cnx->prepare("DELETE FROM contenir WHERE id_e = ?");
            $deleteContenir->execute([$id_e]);

            $deleteEvent = $cnx->prepare("DELETE FROM evenement WHERE id_e = ?");
            $deleteEvent->execute([$id_e]);

            $cnx->commit();
            $_SESSION['flash_message'] = "suppression réussie !";
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

// gestion des ajouts d'évenement publiques.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addEvent'])) {
    try {
        $cnx->beginTransaction();

        $user_id = $_SESSION['u_id'];
        $libelle = $_POST['titre'];
        $date = $_POST['date'];
        $heure = $_POST['heure'];
        $places = $_POST['nbplaces'];

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
        $check = $cnx->prepare("SELECT * FROM evenement WHERE libelle = ? AND date_h = ? AND heure = ? AND nbplaces = ? AND perso = ?");
        $check->execute([$libelle, $date, $heure, $places, 0]);
        if ($check->fetch()) {
            throw new Exception("L'évenement existe déjà !");
        }
        
        // insertion de l'évenement dans la table evenement.
        $event = $cnx->prepare("INSERT INTO evenement (id_e, libelle, date_h, nbplaces, heure, perso) VALUES (?, ?, ?, ?, ?, ?)");
        $event->execute([$event_id, $libelle, $date, $places, $heure, 0]);
        
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
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="admin.css">
        <link rel="stylesheet" href="/color.css">
        <title>Dashboard</title>
    </head>
    <body>
        <?php // affiche un popup  pour indiquer plein de trucs comme l'inscription.
            if(isset($_SESSION['flash_message'])) {
                $message = $_SESSION['flash_message'];
                //echo "<p>" . $_SESSION['flash_message'] . "</p>";
                echo "<script>alert('".$message."');</script>";
                unset($_SESSION['flash_message']);
            }
            
        ?>
        <div class="container-events">
        <div class="confs">
                
                <?php
                // affichage des évenements publics
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
                                        <input type="submit" name="action" class="inscription" value="">
                                    </form>';
                            echo '</div>';
                        }
                    }  
                ?>
            </div>
        </div>

        <div class="container-form">

            <div class="ajout-compte">
                <div class="top">
                    <h1>Créer un nouveau compte</h1>
                </div>
                <div class="bottom">
                    <form action="" method="POST">
                        <input type="text" placeholder="Nom">
                        <input type="text" placeholder="Prénom">
                        <input type="text" placeholder="Identifiant">
                        <input type="text" placeholder="Mot de passe">
                        <div class="bouton">
                            <input type="reset" value="Annuler">
                            <input type="submit" value="Créer">
                        </div>
                    </form>
                </div>
            </div>

            <div class="ajout-event">
                <div class="top">
                    <h1>Créer un nouveau évènement</h1>
                </div>
                <div class="bottom">
                    <form action="" method="POST">
                        <input type="text" placeholder="Titre" name="titre" required>
                        <input type="date" placeholder="Date" name="date" required>
                        <input type="time" placeholder="heure" name="heure" required>
                        <input type="number" placeholder="nb places" name="nbplaces" min="0" value="" required>
                        <div class="bouton">
                            <input type="hidden" name="addEvent" value="">
                            <input type="reset" value="Annuler">
                            <input type="submit" name="addEvent" value="Créer">
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <div class="container-senior">
            <div class="seniors">
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
                <hr>
                <div class="senior">
                    <div class="left">
                        <div class="img" style="background-image: url(/img/cessy.webp);"></div>
                    </div>
                    <div class="middle">
                        <p>Cessy David</p>
                    </div>
                    <div class="right">
                        <form action="">
                            <input type="submit" value="supprimer">
                        </form>
                    </div>
                </div>
            </div>
            
            
        </div>
    </body>
</html>