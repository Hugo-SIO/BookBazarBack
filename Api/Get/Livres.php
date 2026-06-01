<?php
/**
 * Contrôleur REST – GET /Get/Livres.php
 * Retourne la liste complète de tous les livres (annonces),
 * quel que soit leur statut (disponible ou vendu).
 * Utilisé par les pages d'administration.
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

require_once "../../Classes/CLivres.php";

// Récupère tous les livres sans filtre de statut
$livres = CLivres::getInstance()->getLivres();

echo json_encode($livres, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>