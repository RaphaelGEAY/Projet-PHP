<?php
// db.php

/* CONFIGURATION DE LA CONNEXION
   -----------------------------
   Note pour le rendu : 
   Sur ma machine WSL, j'utilise 'admin' et '127.0.0.1'.
   Si vous testez sur XAMPP, changez $host en 'localhost' et $user/$pass en 'root'.
*/

$host = '127.0.0.1';   // Force l'IP pour que ça marche sur WSL
$db   = 'php_exam_db'; // Le nom imposé par le sujet
$user = 'root';       // Ton utilisateur créé tout à l'heure
$pass = 'root'; // Ton mot de passe défini tout à l'heure

try {
    // On crée la connexion
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    
    // On active les erreurs pour voir les problèmes SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // echo "Connexion réussie !"; // Décommenter pour tester
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
