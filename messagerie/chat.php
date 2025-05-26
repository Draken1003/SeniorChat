<?php 
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Chat</title>
    <link rel="stylesheet" href="chat.css">
    <link rel="stylesheet" href="../accueil/header.css">
</head>

<body>
    <?php
    
    include("../connexion.inc.php");
    session_start();
    
    if (isset($_SESSION['identifiant'])) {
        $identifiant= $_SESSION['identifiant'];
        $sql_id= "SELECT id FROM SENIOR WHERE pseudo = '$identifiant'";
        $stmt3= $cnx->query($sql_id);
        $id_sen = $stmt3->fetchColumn();
    } else {
        header("Location: ../login/login.php"); 
        exit();
    }
    

    if (isset($_POST['friendbtn'])) {
        $_SESSION['id_friend'] = $_POST['friendbtn']; // Stocker l'ID de l'ami dans la session
    }
   
    ?>
    <header>
        <div class="seniorchat">
            <img src="../imgs/logo.png" />
        </div>
        <div class="links">
            <a href="../agenda/index.php">
                <img src="../imgs/agenda.png">
            </a>
            <a href="../accueil/accueil.php">
                <img src="../imgs/messages.png">
            </a>
            <a href="../profil/profil.php">
                <img src="../imgs/profil.png">
            </a>
            <form action="" method="post">
                <input type="submit" name="logout" value="">
            </form>
        </div>
    </header>

    <div class="center-page">
        <!-- LES AMIS -->
        <div class="friends">
            <h2>Amis</h2>
            <form method="POST" class="friends-list">
                <?php
                    $sql_friends = "SELECT * FROM SENIOR WHERE id != '$id_sen'";
                    $stmt_friends = $cnx->query($sql_friends);
                    while ($friend = $stmt_friends->fetch(PDO::FETCH_ASSOC)) {
                        $id_friend = $friend['id'];
                        $pdp = $friend['pdp'];
                        $statut = $friend['statut'];
                        echo "<div class='friend-card'>
                                <img src=../profil/$pdp alt='Avatar' class='friend-avatar'>";

                        if ($statut == 'actif') {
                            echo "<div class='statut' style='background-color: #00FF00; width: 10px; height: 10px; border-radius: 100%;'></div>";
                        } else if ($statut == 'pas actif') {
                            echo "<div class='statut' style='background-color: #FF6600; width: 10px; height: 10px; border-radius: 100%;'></div>";
                        } else {
                            echo "<div class='statut' style='background-color: #FF0000; width: 10px; height: 10px; border-radius: 100%;'></div>";
                        }

                        echo "<h3>" . $friend['prenom'] . " " . $friend['nom'] . "</h3>
                            <button type='submit' name='friendbtn' class='start-chat' id='start-chat-button' value='' onclick='showChat(this,$id_friend)'>Chat</button>
                            </div>";
                    }
                ?>
            </form>
        </div>

        <div class="container">


            <div class="chat" id="chatbox">
                <?php

                if (isset($_SESSION['id_friend'])) {
                    $id_friend = $_SESSION['id_friend'];
                    $chat = " SELECT MESSAGE.*, SENIOR.nom, SENIOR.prenom FROM MESSAGE JOIN SENIOR ON SENIOR.id = MESSAGE.id 
                    WHERE (MESSAGE.id = '$id_sen' AND MESSAGE.id_1 = '$id_friend') OR (MESSAGE.id = '$id_friend' AND MESSAGE.id_1 = '$id_sen') ORDER BY MESSAGE.date_h;";

                    $stmt = $cnx->query($chat);

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $senior = $row["prenom"] . " " . $row["nom"];
                        $id_mes = $row["num"];

                        if ($row["id"] == $id_sen && $row["id_1"] == $id_friend) {
                            echo "<br><div class='mes1'> <strong>Vous</strong> <p>" . $row["texte"] .
                                "</p> </div> <div class='horaire' id='horaire1'>" . $row["date_h"] . "</div>";
                        } else if ($row["id"] == $id_friend && $row["id_1"] == $id_sen) {
                            echo "<br><div class='mes2'><strong>$senior</strong> <p> " . $row["texte"] .
                                "</p></div><div class='horaire'>" . $row["date_h"] . "</div>";
                        }
                    }
                } else {
                    echo "<p>Veuillez choisir un ami pour commencer la conversation.</p>";
                }


                // ON ECRIT le message


                if (!empty($_POST["message"])) {


                    date_default_timezone_set('Europe/Paris'); //pr avoir la bonne heure
                    $envoi = $_POST['message'];
                    $dateAuj = date("Y-m-d H:i:s");

                    $reqNum = $cnx->query("SELECT MAX(num) FROM MESSAGE;");
                    $num = $reqNum->fetchColumn() + 1;


                    $msg = "INSERT INTO MESSAGE(num, texte, date_h, id, id_1) VALUES(:num, :texte, :date_h, '$id_sen' , '$id_friend')";

                    // on met des parametres pr eviter de causer des bugs si on met des apostrophes 
                    $stmt2 = $cnx->prepare($msg);
                    $stmt2->execute([
                        ':num' => $num,
                        ':texte' => $envoi,
                        ':date_h' => $dateAuj
                    ]);


                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit;
                }
                ?>


                <div id="scroll-target"></div>
            </div>

            <form method="POST" action="" class="input-message">
                <input type="text" id="message" name="message" placeholder="Tape ton message..." required>
                <button type="submit" id="envoyer-button">
                    <img src="../imgs/send-button.png">
                </button>
            </form>
        </div>

    </div>


    </div>


    <!-- code js pr que on soit directement sur le dernier message de la conversation -->
    <script>
        window.onload = function() {
            const target = document.getElementById("scroll-target");
            if (target) {
                target.scrollIntoView();
            }
        };

        //code pr load le chat
        const showChat = (btn, seniorID) => {
            btn.value = seniorID;
        };
    </script>





</body>

</html>