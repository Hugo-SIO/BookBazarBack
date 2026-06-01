<?php
/**
 * Contrôleur REST – GET /Get/Categories.php
 * Retourne la liste de toutes les catégories de livres.
 * Utilisé pour alimenter les filtres et le formulaire
 * d'ajout/modification d'une annonce.
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

require_once "../../Classes/CCategories.php";

// Récupère toutes les catégories via le Singleton CCategories
$categories = CCategories::getInstance()->getCategories();

echo json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>