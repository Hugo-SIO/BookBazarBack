<?php
/**
 * Contrôleur REST – GET /Get/LivresDisponibles.php
 * Retourne uniquement les livres dont le statut est "disponible"
 * (non encore vendus).
 * Utilisé sur la page d'accueil et la page de recherche
 * pour n'afficher que les annonces actives aux acheteurs.
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

// Contrairement à getLivres(), getLivresDisponible() filtre
// en BDD (ou en mémoire) sur le champ statut = 'disponible'
$livresDisponible = CLivres::getInstance()->getLivresDisponible();

echo json_encode($livresDisponible, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>