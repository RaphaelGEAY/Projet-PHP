<?php
// Démarre la session pour pouvoir la détruire
session_start();

// Vide toutes les données de session
$_SESSION = [];

// Supprime le cookie de session si présent
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Détruit la session
session_destroy();

// Redirige vers la page de connexion
header('Location: login.php');
exit;
