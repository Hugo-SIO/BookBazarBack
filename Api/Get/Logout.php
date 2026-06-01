<?php
/**
 * Contrôleur REST – POST /Auth/Logout.php
 * Déconnecte l'utilisateur côté serveur en détruisant sa session PHP.
 * Côté React, il faudra également supprimer le JWT du localStorage.
 *
 * Note : avec une authentification purement JWT (stateless),
 * la session PHP n'est pas strictement nécessaire — le token
 * expire de lui-même après 1h. Mais on la détruit quand même
 * par mesure de sécurité si elle existe.
 */

// Origines autorisées (CORS)
$allowed_origins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

// Vide toutes les variables de session
$_SESSION = [];

// Supprime le cookie de session côté navigateur
// (si les sessions utilisent des cookies)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000, // Date dans le passé → force l'expiration du cookie
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Détruit la session côté serveur
session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Déconnexion réussie"
]);
?>