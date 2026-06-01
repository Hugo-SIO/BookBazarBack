<?php
/**
 * Contrôleur REST – GET /Get/Utilisateurs.php
 * Retourne la liste complète de tous les utilisateurs inscrits.
 * Utilisé par l'interface d'administration (gestion des comptes).
 *
 * ⚠️ Attention : cette route est publique dans le code actuel.
 * En production, elle devrait être protégée par un JWT
 * avec vérification du rôle admin, pour ne pas exposer
 * les données de tous les utilisateurs.
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

require_once "../../Classes/CUtilisateurs.php";

// Récupère tous les utilisateurs via le Singleton CUtilisateurs
$utilisateurs = CUtilisateurs::getInstance()->getUtilisateur();

echo json_encode($utilisateurs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>