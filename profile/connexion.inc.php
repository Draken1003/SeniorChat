<?php

$host = 'localhost';  
$dbname = 'seniordatabase';
$user = 'enzo';
$pass = 'postgres';

try {
    // Utilise le driver PostgreSQL (pgsql) au lieu de MySQL
    $cnx = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);

    //
    //if ($cnx){
    //    echo "Connexion reussi";
    //}
  
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    
}


?>

