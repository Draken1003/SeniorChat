<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="profil.css" />
    <link rel="stylesheet" href="../color.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Bungee&family=Cal+Sans&family=Fredoka:wght@300..700&family=Nuosu+SIL&family=Platypi:ital,wght@0,300..800;1,300..800&family=Quicksand:wght@300..700&family=Zen+Old+Mincho&display=swap"
      rel="stylesheet"
    />
    <title>Profil</title>
  </head>
  <body>

    <?php
      include("../connexion.inc.php");
      session_start();

      if (isset($_SESSION['identifiant'])) {
        $identifiant= $_SESSION['identifiant'];
        $sql_id = "SELECT id FROM senior WHERE pseudo = '$identifiant'";
        $stmt3= $cnx->query($sql_id);
        $id_sen = $stmt3->fetchColumn();
      } else {
          header("Location: ../login/login.php");
          exit();
      }

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $nom = $_POST['nom'];
          $prenom = $_POST['prenom'];
          $pseudo = $_POST['pseudo'];
          $tel = $_POST['tel'];
          $birth_date = $_POST['date_n'];
          $nvStatut = $_POST['statut'];

          $sql2 = "UPDATE SENIOR SET nom = '$nom', prenom = '$prenom', pseudo = '$pseudo', tel = '$tel', date_n = '$birth_date', statut='$nvStatut' WHERE id = '$id_sen'";
          $stmt2 = $cnx->prepare($sql2);
          $stmt2->execute();

          if (isset($_FILES['pfp']) && $_FILES['pfp']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
              mkdir($uploadDir, 0777, true);
            }
            $newFileName = $id_sen . '.jpg';
            $destination = $uploadDir . $newFileName;
            if(move_uploaded_file($_FILES['pfp']['tmp_name'], $destination)) {
              $sql = "UPDATE SENIOR SET pdp = '$destination' WHERE id = '$id_sen'";
              $stmt = $cnx->prepare($sql);
              $stmt->execute();
            }
          }
      }

      $sqlP= "SELECT pdp FROM SENIOR WHERE id='$id_sen'";
      $stmtP= $cnx->prepare($sqlP);
      $stmtP->execute();
      $pdp = $stmtP->fetchColumn();
    ?>

    <div class="page">
      <header>
        <h1>Profil</h1>
      </header>
      <div class="center-page">

        <form method="post" enctype="multipart/form-data">

          <div class="photo">
            <?php
              echo "<img src='$pdp' id='pfp'/>";
              echo "<label>Charger votre image</label> <input id='pfpI' type='file' name='pfp'/>";
            ?>
            <div class="buttons">
              <button type="button" id="edit-btn">Modifier</button>
              <button type="reset" >Supprimer</button>
            </div>
          </div>

          <div class="bot-infos">

            <?php
              $sql= "SELECT * FROM SENIOR WHERE id='$id_sen';";
              $stmt= $cnx->query($sql);
              while ($senior = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $nom= $senior['nom'];
                $prenom=$senior['prenom'];
                $pseudo= $senior['pseudo'];
                $tel= $senior['tel'];
                $date_n= $senior['date_n'];
                $statut= $senior['statut'];
                
                echo " <div class='infos'> <div class='right-infos'> <div class='title-infos'> <h2> Nom </h2> </div> <input type='text' name='nom' value='$nom' readonly='readonly' /> </div> </div>";
                echo " <div class='infos'> <div class='right-infos'> <div class='title-infos'> <h2> Prenom </h2> </div>  <input type='text' name='prenom' value='$prenom' readonly='readonly' />  </div> </div>";
                echo " <div class='infos'> <div class='right-infos'> <div class='title-infos'> <h2> Pseudo </h2> </div> <input type='text' name='pseudo' value='$pseudo' readonly='readonly' />  </div> </div>";
                echo " <div class='infos'> <div class='right-infos'> <div class='title-infos'> <h2> Telephone </h2> </div>  <input type='text' name='tel' value='$tel' readonly='readonly' />  </div> </div>";
                echo " <div class='infos'> <div class='right-infos'> <div class='title-infos'> <h2> Date de Naissance </h2> </div>  <input type='text' name='date_n' value='$date_n' readonly='readonly' /> </div> </div>";
                echo " <div class='infos'> <div class='right-infos'> <div class='title-infos'> <h2>Statut</h2> </div> <select name='statut'> <option value='actif'" . ($statut == 'actif' ? ' selected' : '') . ">Actif</option> <option value='pas actif'" . ($statut == 'pas actif' ? ' selected' : '') . ">Pas actif</option> <option value='ne pas déranger'" . ($statut == 'ne pas déranger' ? ' selected' : '') . ">Ne pas déranger</option> </select> </div> </div>";
              }
            ?>

            <div class="valider">
              <button type="submit">Valider</button>
            </div>
          </div>
        </form>

      </div>
    </div>

    <script>
      //pour pouvoir afficher l'image quand on la choisit ds notre dossier
      document.addEventListener('DOMContentLoaded', function() {
        const inputFile = document.getElementById('pfpI');
        if(inputFile){
          inputFile.addEventListener('change', function(){
            const file = this.files[0];
            if(file){
              const reader = new FileReader();
              reader.onload = function(e){
                document.getElementById('pfp').src = e.target.result;
              }
              reader.readAsDataURL(file);
            }
          });
        }
      });

      //pour pouvoir modifier
      document.getElementById('edit-btn').addEventListener('click', function () {
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => input.removeAttribute('readonly'));
      });

      
    </script>
  </body>
</html>
