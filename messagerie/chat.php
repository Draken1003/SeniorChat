<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Senior Chat</title>
        <link rel="stylesheet" href="chat.css">
    </head>
    <body>
        <?php
            include("../connexion.inc.php");
            

            session_start();
            
            if (isset($_SESSION['identifiant'])) {

                $identifiant= $_SESSION['identifiant'];
                $stmt3 = $cnx->prepare("SELECT id FROM SENIOR WHERE pseudo = :pseudo");
                $stmt3->execute([':pseudo' => $identifiant]);
                // ptet à modif ici ci dessus
                $id_sen = $stmt3->fetchColumn();
            } 
            if (isset($_POST['friendbtn'])) {
                $_SESSION['id_friend'] = $_POST['friendbtn']; // Stocker l'ID de l'ami dans la session
                echo $_SESSION['id_friend'];

            }
        ?>

        <!-- LES AMIS -->
        <div class="friends">
            <h2>Amis</h2>
            <form method="POST" class="friends-list">
                <?php
                    //$sql_friends= "";
                    $stmt_friends= $cnx->query("SELECT * FROM SENIOR WHERE id != '$id_sen'");
                    while ($friend = $stmt_friends->fetch(PDO::FETCH_ASSOC)) {
                        $id_friend= $friend['id'];
                        echo "<div class='friend-card'>
                                <img src='cessy.webp' alt='Avatar' class='friend-avatar'>
                                <h3>" . $friend['prenom'] . " " . $friend['nom'] . "</h3>
                                <p>" . $friend['pseudo'] . "</p>
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
                    $id_mes= $row["num"];

                    if ($row["id"] == $id_sen && $row["id_1"] == $id_friend) {
                        echo "<br><div class='mes1'><strong>$senior</strong> " . $row["texte"] . 
                            "<div class='horaire'>" . $row["date_h"] . "</div> <button onclick='openPopup($id_mes)' class='delete-msg-left'>Supprimer</button> </div>"; 

                    } else if ($row["id"] == $id_friend && $row["id_1"] == $id_sen) {
                        echo "<br><div class='mes2'><strong>$senior</strong> " . $row["texte"] . 
                            "<div class='horaire'>" . $row["date_h"] . "</div></div>"; 
                    }
                }
            }else { 
            
                echo "<p>Veuillez choisir un ami pour commencer la conversation.</p>";
            } 

            // ON ECRIT le message
            if (!empty($_POST["message"])) {


                date_default_timezone_set('Europe/Paris'); //pr avoir la bonne heure
                $envoi = $_POST['message'];
                $dateAuj = date("Y-m-d H:i:s");
            
                $reqNum = $cnx->query("SELECT MAX(num) FROM MESSAGE;");
                $num = $reqNum->fetchColumn() +1;
            
            
                $msg = "INSERT INTO MESSAGE(num, texte, date_h, id, id_1) VALUES(:num, :texte, :date_h, '$id_sen' , '$id_friend')";
                
                // on met des parametres pr eviter de causer des bugs si on met des apostrophes 
                $stmt2 = $cnx->prepare($msg);
                $stmt2->execute([
                    ':num' => $num,           
                    ':texte' => $envoi,       
                    ':date_h' => $dateAuj   
                ]);


                header("Location: " . $_SERVER['PHP_SELF']); // permet de redireger vers la même page ( pas besoin de refresh)
                exit;
            }
        
            // ON SUPPRIME LE MESSAGE
            if (isset($_POST['delete_message'])) {
                $messageId = $_POST['delete_message'];
            

                $sql4 = "DELETE FROM MESSAGE WHERE num = :messageId";
                $stmt4 = $cnx->prepare($sql4);
                $stmt4->execute([':messageId' => $messageId]);

                header("Location: " . $_SERVER['PHP_SELF']); // permet de redireger vers la même page ( pas besoin de refresh)
                exit;
            
            }
            
            ?>

            <!-- popup POUR SUPPRIMER LES MESSAGES -->
            <div class="overlay-popup">
                <div class="content-popup">
                    <h3>Voulez vous vraiment supprimer le message ?</h3>
                    <form method="POST" class="buttons">
                        <button type="submit" name="delete_message" id="delete_message_button">Oui</button>
                        <button onclick="closePopup()" type="button">Non</button>
                    </form>
                </div>
            </div>

                <div id="scroll-target"></div>
            </div>

            <form method="POST" action="">
                <input type="text" id="message" name="message" placeholder="Tape ton message..." required>
                <button type="submit">Envoyer</button>
            </form>

        </div>


        <!-- code js pr que on soit directement sur le dernier message de la conversation -->
        <script>
            window.onload = function() {
                const target = document.getElementById("scroll-target");
                if (target) {
                    target.scrollIntoView();
                }
            };
            //  code js pr le popup suppr message

            const openPopup = (messageId) => {
            const popup = document.querySelector(".overlay-popup");
            popup.classList.add('active-popup'); 

            const deleteButton = document.getElementById("delete_message_button");
            deleteButton.value = messageId;
            };

            const closePopup = () => {
            const popup = document.querySelector(".overlay-popup");
            popup.classList.remove('active-popup'); 
            };

            //code pr load le chat

            const showChat= (btn,seniorID) => {
                btn.value=seniorID;
            };

   
        </script> 
    </body>
</html>