<?php
/**
 * Contrôleur REST – GET /Get/Roles.php
 * Retourne la liste de tous les rôles disponibles (ex: admin, utilisateur).
 * Utilisé dans l'interface d'administration pour afficher
 * ou modifier le rôle d'un utilisateur.
 * Route publique : aucun JWT requis.
 */

// Origines autorisées (CORS)
$allowed_origins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../../Classes/CRoles.php";

// Récupère tous les rôles via le Singleton CRoles
$roles = CRoles::getInstance()->getRole();

echo json_encode($roles, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>