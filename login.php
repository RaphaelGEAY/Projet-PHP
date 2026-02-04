<?php

// On démarre la session
session_start();
require_once 'db.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(); // On récupère la ligne correspondante

    // 2. On vérifie si l'utilisateur existe ET si le mot de passe est bon
    // password_verify compare le texte clair avec le "hachis" (hash) de la base
    if ($user && password_verify($password, $user['password'])) {

        // 4. Connecter automatiquement l'utilisateur
        $_SESSION['user_id'] = $user["id"];
        $_SESSION['email'] = $user["email"];
        $_SESSION['role'] = $user["role"];

        // Redirection vers l'accueil
        header('Location: index.php');
        exit;
    } else {
        $error = "Identifiants invalides.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
</head>
<body>
    <h1>Connexion</h1>
    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Mot de passe" required><br>
        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
