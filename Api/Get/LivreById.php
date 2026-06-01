<?php
/**
 * Contrôleur REST – GET /Get/LivreById.php?idAnnonce=12
 * Retourne les détails complets d'un livre précis.
 * Utilisé sur la page détail d'une annonce.
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

// Validation stricte du paramètre GET : doit exister et être numérique
if (!isset($_GET['idAnnonce']) || !is_numeric($_GET['idAnnonce'])) {
    http_response_code(400);
    echo json_encode(["error" => "idAnnonce manquant ou invalide"]);
    exit;
}

// Cast en int pour sécuriser la valeur avant de la passer à la BDD
$idAnnonce = (int) $_GET['idAnnonce'];

// Récupère un unique livre par son id via la méthode métier
$livre = CLivres::getInstance()->getLivreById($idAnnonce);

if (!$livre) {
    http_response_code(404);
    echo json_encode(["error" => "Livre non trouvé"]);
    exit;
}

echo json_encode($livre, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>