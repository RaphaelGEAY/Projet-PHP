<?php

// On démarre la session
session_start();
require_once 'db.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 2. Hasher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // 3. Insérer dans la base
    $sql = "INSERT INTO users (nom, prenom, email, password, role, balance) VALUES (:nom, :prenom, :email, :password, 'user', 0)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':password' => $hashed_password
        ]);

        // 4. Connecter automatiquement l'utilisateur
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'user';

        // Redirection vers l'accueil
        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        $error = "Erreur lors de l'inscription : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
</head>
<body>
    <h1>Créer un compte</h1>
    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    
    <form method="POST">
        <input type="text" name="nom" placeholder="Nom" required><br>
        <input type="text" name="prenom" placeholder="Prenom" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br>
        <button type="submit">S'inscrire</button>
    </form>
</body>
</html>
