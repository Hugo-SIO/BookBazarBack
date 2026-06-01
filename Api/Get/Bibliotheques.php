<?php
/**
 * Contrôleur REST – GET /Get/Bibliotheques.php
 * Retourne la liste complète de toutes les bibliothèques
 * (livres possédés par les utilisateurs).
 * Route publique : aucun JWT requis.
 */

// Origines autorisées (CORS)
$allowedOrigins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
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

require_once "../../Classes/CBibliotheques.php";

// Récupère toutes les bibliothèques via le Singleton CBibliotheques
$bibli = CBibliotheques::getInstance()->getBibliotheque();

// JSON_UNESCAPED_UNICODE : évite l'encodage des accents (é → \u00e9)
// JSON_PRETTY_PRINT : formate le JSON lisiblement (utile en debug)
echo json_encode($bibli, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>