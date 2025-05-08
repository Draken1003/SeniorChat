<?php
    // on vérifie que le formulaire à été posté car sinon il s'execute dès le début
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include("../connexion.inc.php");
        session_start();
        $id = $_POST["identifiant"];
        $mdp = $_POST["mdp"];
                        
        // On vérifie les identifiants
        $verif = $cnx->prepare("SELECT identifiant, id FROM authentification WHERE identifiant = :id AND password = :mdp");
        $verif->bindParam(':id', $id);
        $verif->bindParam(':mdp', $mdp);
        $verif->execute();
                        
        if ($verif->rowCount() > 0) {

            $row = $verif->fetch();
            $_SESSION['u_id'] = $row['id'];
            $_SESSION['identifiant'] = $row['identifiant'];

            header("Location: ../agenda/agenda.html"); // juste pour test donc faudrai mettre la vrai page
            exit;
        } else {
            $error = "<p class='errorMessage'>identifiant ou mot de passe incorrect</p>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../color.css">
        <link rel="stylesheet" href="login.css">
        <title>SeniorChat</title>
    </head>
    <body>
        <div class="right-part">
            <div class="title">
                <p>Bonjour</p>
                <p>et bienvenue !</p>
            </div>
            <div class="login-space">
                <p class="sentence">Connectez-vous:</p>
                <form action="" method="POST">
                    <div class="info-client">
                        <p class="fields-title">Identifiant</p>
                        <input type="text" name="identifiant" required>
                        <p class="fields-title">Mot de passe</p>
                        <input type="password" name="mdp" required>
                        <a class="mdp-oublie" href="#">Mot de passe oublié ?</a>
                        <input type="submit" value="Se connecter">
                    </div>
                </form>
                <div class="bottom-login-space">
                    <p>Vous n'avez pas de compte ?</p>
                    <a href="../inscription/inscription.html">Créer un compte</a>
                    <?php if (!empty($error)) echo "<p class='errorMessage'>$error</p>"; ?>
                </div>
            </div>
        </div>
    </body>
</html>