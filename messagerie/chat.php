<?php
session_start();
include("../connexion.inc.php");

// on se delog et on detruit la session par la meme occasion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["logout"])) {
    session_unset();
    session_destroy();
    header("Location: ../login/login.php");
    exit;
}

// si on essaye d'aller sur la page ss etre log on redirige vers login
if (!isset($_SESSION['identifiant'])) {
    header("Location: ../login/login.php");
    exit();
}

$identifiant = $_SESSION['identifiant'];

$sql_id = "SELECT id FROM SENIOR WHERE pseudo = :pseudo";
$stmt3 = $cnx->prepare($sql_id);
$stmt3->execute([':pseudo' => $identifiant]);
$id_sen = $stmt3->fetchColumn();

// si on clique sur chat on recupere l'id du friend pr afficher la conv 
if (isset($_POST['friendbtn'])) {
    $_SESSION['id_friend'] = $_POST['friendbtn'];
}

// on envoie le message
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['message']) && isset($_SESSION['id_friend'])) {
    $id_friend = $_SESSION['id_friend'];
    $envoi = trim($_POST['message']);
    if ($envoi !== '') {
        date_default_timezone_set('Europe/Paris');
        $dateAuj = date("Y-m-d H:i:s");

        $reqNum = $cnx->query("SELECT MAX(num) FROM MESSAGE;");
        $num = $reqNum->fetchColumn();
        $num = $num ? $num + 1 : 1;

        $msg = "INSERT INTO MESSAGE(num, texte, date_h, id, id_1) VALUES(:num, :texte, :date_h, :id_sen, :id_friend)";
        $stmt2 = $cnx->prepare($msg);
        $stmt2->execute([
            ':num' => $num,
            ':texte' => $envoi,
            ':date_h' => $dateAuj,
            ':id_sen' => $id_sen,
            ':id_friend' => $id_friend
        ]);

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// on charge les messages avec ajax - >  methode get
if (isset($_GET['action']) && $_GET['action'] == 'load_messages') {
    if (!isset($_SESSION['id_friend'])) {
        echo "<p>Veuillez choisir un ami pour commencer la conversation.</p>";
        exit;
    }
    $id_friend = $_SESSION['id_friend'];

    $chat = "SELECT MESSAGE.*, SENIOR.nom, SENIOR.prenom 
             FROM MESSAGE 
             JOIN SENIOR ON SENIOR.id = MESSAGE.id 
             WHERE (MESSAGE.id = :id_sen AND MESSAGE.id_1 = :id_friend) 
                OR (MESSAGE.id = :id_friend AND MESSAGE.id_1 = :id_sen) 
             ORDER BY MESSAGE.date_h";

    $stmt = $cnx->prepare($chat);
    $stmt->execute([':id_sen' => $id_sen, ':id_friend' => $id_friend]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $senior = $row["prenom"] . " " . $row["nom"];
        $texte = $row["texte"];
        $date_h = $row["date_h"];

        if ($row["id"] == $id_sen && $row["id_1"] == $id_friend) {
            echo "<div class='mes1'><strong>Vous</strong><p>$texte</p></div><div class='horaire' id='horaire1'>$date_h</div>";
        } else {
            echo "<div class='mes2'><strong>$senior</strong><p>$texte</p></div><div class='horaire'>$date_h</div>";
        }
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Senior Chat</title>
    <link rel="stylesheet" href="chat.css" />
    <link rel="stylesheet" href="../accueil/header.css" />
</head>

<body>
    <header>
        <div class="seniorchat">
            <img src="../imgs/logo.png" />
        </div>
        <div class="links">
            <a href="../agenda/index.php"><img src="../imgs/agenda.png" /></a>
            <a href="../accueil/accueil.php"><img src="../imgs/messages.png" /></a>
            <a href="../profil/profil.php"><img src="../imgs/profil.png" /></a>
            <form action="" method="post">
                <input type="submit" name="logout" value=""/>
            </form>
        </div>
    </header>

    <div class="center-page">
        <div class="friends">
            <h2>Amis</h2>
            <form method="POST" class="friends-list">

                <?php
                $sql_friends = "SELECT * FROM SENIOR WHERE id != :id_sen";
                $stmt_friends = $cnx->prepare($sql_friends);
                $stmt_friends->execute([':id_sen' => $id_sen]);
                while ($friend = $stmt_friends->fetch(PDO::FETCH_ASSOC)) {
                    $id_friend = $friend['id'];
                    $pdp = htmlspecialchars($friend['pdp']);
                    $statut = $friend['statut'];

                    echo "<div class='friend-card'>
                            <img src='../profil/$pdp' alt='Avatar' class='friend-avatar'>";
                    $color = '#FF0000';
                    if ($statut == 'actif') $color = '#00FF00';
                    elseif ($statut == 'pas actif') $color = '#FF6600';

                    echo "<div class='statut' style='background-color: $color; width: 10px; height: 10px; border-radius: 100%;'></div>";
                    echo "<h3>" . htmlspecialchars($friend['prenom']) . " " . htmlspecialchars($friend['nom']) . "</h3>
                        <button type='submit' name='friendbtn' class='start-chat' value='$id_friend'>Chat</button>
                    </div>";
                }
                ?>

            </form>
        </div>

        <div class="container">
            <div class="chat" id="chatbox">

                <?php
                if (!isset($_SESSION['id_friend'])) {
                    echo "<p>Veuillez choisir un ami pour commencer la conversation.</p>";
                }
                ?>

            </div>

            <form method="POST" action="" class="input-message">
                <input type="text" id="message" name="message" placeholder="Tape ton message..." required />
                <button type="submit" id="envoyer-button">
                    <img src="../imgs/send-button.png" />
                </button>
            </form>
        </div>
    </div>

    <script>

        //fonction pr load les messages et scroll en bas pr voir le dernier message
        function loadMessages() {
            fetch('chat.php?action=load_messages')
                .then(response => response.text())
                .then(data => {
                    const chatbox = document.getElementById('chatbox');
                    chatbox.innerHTML = data;
                    chatbox.scrollTop = chatbox.scrollHeight;
                });
        }

        setInterval(loadMessages, 2000);
        window.onload = loadMessages;
    </script>
</body>

</html>
