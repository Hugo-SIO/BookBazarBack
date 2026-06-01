<?php
/**
 * Contrôleur REST – POST /Get/GetAcheteur.php
 * Retourne les informations de l'acheteur d'une annonce donnée.
 * Utilisé pour afficher qui a acheté un livre mis en vente.
 * Route publique : aucun JWT requis.
 *
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

// Lecture du corps JSON : React envoie { "idAnnonce": 12 }
$data      = json_decode(file_get_contents("php://input"), true);
$idAnnonce = $data['idAnnonce'] ?? null;

if (!$idAnnonce) {
    http_response_code(400);
    echo json_encode(["error" => "idAnnonce manquant"]);
    exit;
}

require_once "../../Classes/CLivres.php";

// Récupère l'acheteur associé à cette annonce via la méthode métier
$acheteur = CLivres::getInstance()->getAcheteur($idAnnonce);

echo json_encode($acheteur, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>