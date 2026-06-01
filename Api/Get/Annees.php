<?php
/**
 * Contrôleur REST – GET /Get/Annees.php
 * Retourne la liste de toutes les années de parution
 * des livres disponibles dans la base de données.
 * Utilisé par le frontend React pour alimenter
 * un filtre ou une liste déroulante d'années.
 * Route publique : aucun token JWT requis.
 */

// Origines autorisées (CORS)
$allowedOrigins = [
    "http://localhost:5173",       // React en développement local
    "https://bookbazar.hugoal.fr"  // Frontend en production
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
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

// Récupère tous les livres via le Singleton CLivres
$livres = CLivres::getInstance()->getLivres();

// On extrait uniquement l'année de parution de chaque livre
// en appelant le getter de l'objet CLivre
$collAnnees = [];
foreach ($livres as $livre) {
    $collAnnees[] = $livre->getAnneeParution();
}

echo json_encode($collAnnees);
?>