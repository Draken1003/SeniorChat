<?php
session_start();
include("../connexion.inc.php");

// Vérification de session et logout
if (!isset($_SESSION['identifiant'])) {
    if ($_SESSION['identifiant'] != 'admin') {
        header("Location: ../login/login.php");
        exit;
    } 
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

//On supprime le senior
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['suppr'])) {
    $idSup = $_POST['supprId'];

    $sqlAuth = "DELETE FROM AUTHENTIFICATION WHERE id = '$idSup'";
    $stmtAuth = $cnx->prepare($sqlAuth);

    $sqlSuppr = "DELETE FROM SENIOR WHERE id = '$idSup'";
    $stmtSuppr = $cnx->prepare($sqlSuppr);

    $sqlAgenda = "DELETE FROM AGENDA WHERE id_a = '$idSup'";
    $stmtAgenda = $cnx->prepare($sqlAgenda);
    
    $sqlMessage = "DELETE FROM MESSAGE WHERE id = '$idSup' OR id_1 = '$idSup'";
    $stmtMessage = $cnx->prepare($sqlMessage);

    $stmtAuth->execute();       
    $stmtSuppr->execute();          
    $stmtAgenda->execute();
    $stmtMessage->execute();

    header("Location: " . $_SERVER['PHP_SELF']); // permet de redireger vers la même page ( pas besoin de refresh)
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./admin.css">
    <link rel="stylesheet" href="../color.css">
    <title>Dashboard Admin</title>
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

    <div class="admin-container">
        <!-- Menu latéral -->
        <div class="sidebar">
            <h1>Administration</h1>
            <div class="menu-section">
                <h2>Gestion des comptes</h2>
                <ul>
                    <li class="active" data-section="compte-creer">Créer un compte</li>
                    <li data-section="compte-liste">Liste des comptes</li>
                </ul>
            </div>
            <div class="menu-section">
                <h2>Gestion des activités</h2>
                <ul>
                    <li data-section="activite-creer">Créer une activité</li>
                    <li data-section="activite-liste">Liste des activités</li>
                </ul>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="main-content">
            <!-- Section création de compte -->
            <div id="compte-creer" class="content-section active">
                <div class="ajout-compte">
                    <div class="top">
                        <h1>Créer un nouveau compte</h1>
                    </div>
                    <div class="bottom">
                        <form action="" method="POST">
                            <div class="top-input">
                                <input type="text" name="nom" placeholder="Nom">
                                <input type="text" name="prenom" placeholder="Prénom">
                                <input type="text" name="identifiant" placeholder="Identifiant">
                                <input type="text" name="motdepasse" placeholder="Mot de passe">
                            </div>
                            <div class="bouton">
                                <input type="reset" value="Annuler">
                                <input type="submit" name="creer" value="Créer">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php
            //CREER un senior
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer'])) {

                $req = $cnx->query("SELECT MAX(id) AS last_num FROM senior");
                $res = $req->fetch();
                $newNum = $res['last_num'] + 1;

                $sqlAgenda = "INSERT INTO agenda (id_a) VALUES ('$newNum')";
                $stmtAgenda = $cnx->prepare($sqlAgenda);
                $stmtAgenda->execute();

                $nom = $_POST['nom'];
                $prenom = $_POST['prenom'];
                $sqlSenior = "INSERT INTO senior (id, nom, prenom,id_a) VALUES ('$newNum', '$nom', '$prenom','$newNum')";
                $stmt = $cnx->prepare($sqlSenior);
                $stmt->execute();

                $id = $_POST['identifiant'];
                $pass = $_POST['motdepasse'];
                $sqlAuth = "INSERT INTO AUTHENTIFICATION (identifiant, password, id) VALUES ('$id', '$pass', '$newNum')";
                $stmtAuth = $cnx->prepare($sqlAuth);
                $stmtAuth->execute();

                echo "Compte créé.";
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            }

            ?>

            <!-- Section liste des comptes -->
            <div id="compte-liste" class="content-section">
                <div class="container-senior">
                    <div class='seniors'>
                        <?php
                        
                        //on affiche les seniors
                        $sqlSeniors = "SELECT * FROM SENIOR";
                        $stmtSen = $cnx->query($sqlSeniors);

                        while ($senior = $stmtSen->fetch(PDO::FETCH_ASSOC)) {
                            $pdp = $senior['pdp'];
                            $prenomSen = $senior['prenom'];
                            $nomSen = $senior['nom'];
                            $idSen = $senior['id'];
                            echo "<div class='senior'> <div class='img' style='background-image: url(../profil/$pdp);'></div>
                            <p> $prenomSen $nomSen</p> <div class='right'> <form action='' method='POST' id='supr-compte'> <input type='hidden' name='supprId' value=$idSen> <input type='submit' name='suppr' value='Supprimer'> </form> </div></div> <hr>";
                        }
                        
                        ?>
                    </div>
                </div>
            </div>

            <!-- Section création d'activité -->
            <div id="activite-creer" class="content-section">
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

            <!-- Section liste des activités -->
            <div id="activite-liste" class="content-section">
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
            </div>
        </div>
    </div>

    <script>
        // Gestion du menu latéral
        document.querySelectorAll('.sidebar li').forEach(item => {
            item.addEventListener('click', function() {
                // Retire la classe active de tous les éléments
                document.querySelectorAll('.sidebar li').forEach(i => i.classList.remove('active'));
                document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));

                // Ajoute la classe active à l'élément cliqué
                this.classList.add('active');
                const sectionId = this.getAttribute('data-section');
                document.getElementById(sectionId).classList.add('active');
            });
        });
    </script>
</body>

</html>