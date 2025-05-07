<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="chat.css">
    </head>
    <body>
        <?php
            include("connexion.inc.php");
        ?>

        <div class="container">
            <div class="chat" id="chatbox">
            <?php

                $chat = " SELECT MESSAGE.*, SENIOR.nom, SENIOR.prenom FROM MESSAGE JOIN SENIOR ON SENIOR.id = MESSAGE.id 
                WHERE (MESSAGE.id = 1 AND MESSAGE.id_1 = 2) OR (MESSAGE.id = 2 AND MESSAGE.id_1 = 1) ORDER BY MESSAGE.date_h;";

                $stmt = $cnx->query($chat);

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $senior = $row["prenom"] . " " . $row["nom"];
                    $id_mes= $row["num"];

                    if ($row["id"] == 1 && $row["id_1"] == 2) {
                        echo "<br><div class='mes1'><strong>$senior</strong> " . $row["texte"] . 
                            "<div class='horaire'>" . $row["date_h"] . "</div> <button onclick='openPopup($id_mes)' class='delete-msg-left'>Supprimer</button> </div>"; 

                    } else if ($row["id"] == 2 && $row["id_1"] == 1) {
                        echo "<br><div class='mes2'><strong>$senior</strong> " . $row["texte"] . 
                            "<div class='horaire'>" . $row["date_h"] . "</div></div>"; 
                    }
                }

                ?>

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

    

        <?php
        if (!empty($_POST["message"])) {


            date_default_timezone_set('Europe/Paris'); //pr avoir la bonne heure
            $envoi = $_POST['message'];
            $dateAuj = date("Y-m-d H:i:s");
        
            $reqNum = $cnx->query("SELECT MAX(num) FROM MESSAGE;");
            $num = $reqNum->fetchColumn() +1;
        
        
            $msg = "INSERT INTO MESSAGE(num, texte, date_h, id, id_1) VALUES(:num, :texte, :date_h, 1, 2)";
            
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

        if (isset($_POST['delete_message'])) {
            $messageId = $_POST['delete_message'];
        
    
            $sql4 = "DELETE FROM MESSAGE WHERE num = :messageId";
            $stmt4 = $cnx->prepare($sql4);
            $stmt4->execute([':messageId' => $messageId]);
        
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
        ?>


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

   
        </script>




        
    </body>
</html>