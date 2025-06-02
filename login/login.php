<?php
    include("../connexion.inc.php"); // fichier pour la connection à la base de données.
    // on vérifie que le formulaire à été posté car sinon il s'execute dès le début
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        session_start();
        $id = $_POST["identifiant"];
        $mdp = $_POST["mdp"];            
        // On vérifie les identifiants
        $verif = $cnx->prepare("SELECT id, identifiant FROM authentification WHERE identifiant = :id AND password = :mdp");
        $verif->bindParam(':id', $id);
        $verif->bindParam(':mdp', $mdp);
        $verif->execute();
        
        // si il y a une ligne qui correspond bien à notre identifiant 
        // on regarde si c'est un admin sinon on le connecte à son espace
        if ($verif->rowCount() > 0) {
            $row = $verif->fetch();
            if ($id != 'admin') {
                $_SESSION['u_id'] = $row['id'];
                $_SESSION['identifiant'] = $row['identifiant'];

                header("Location: ../accueil/accueil.php");
                exit;
            } else { // si c'est un admin, on le connecte à son espace
                $_SESSION['u_id'] = $row['id'];
                $_SESSION['identifiant'] = $row['identifiant'];

                header("Location: ../admin/admin.php");
                exit;
            }
            
        } else { // si le mot de passe ou l'identifiant n'est pas bon
            $error = "<p class='errorMessage'>identifiant ou mot de passe incorrect</p>";
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../color.css">
    <link rel="stylesheet" href="login.css">
    <title>SeniorChat</title>
</head>
    <body>
        <div class="left-part">
            <img src="../imgs/logoLogin.png" alt="logo">
        </div>
        <div class="right-part">
            <div class="title">
                <p>Bonjour</p>
                <p>et bienvenue !</p>
            </div>
            <div class="login-space">
                <p class="sentence">Connectez-vous:</p>
                <form action="" method="POST">
                    <div class="sign-container">
                        <div class="info-client">
                            <p class="fields-title">Identifiant</p>
                            <input type="text" name="identifiant" required>
                            <p class="fields-title">Mot de passe</p>
                            <input type="password" name="mdp" required>
                            <a class="mdp-oublie" href="#">Mot de passe oublié ?</a>
                            <input type="submit" value="Se connecter">
                        </div>
                    </div>
                </form>
                <div class="bottom-login-space">
                    <?php if (!empty($error)) echo "$error"; ?>
                </div>
            </div>
        </div>
    </body>
</html>
