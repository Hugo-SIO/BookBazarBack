<?php
/**
 * Contrôleur REST – GET /Get/GetSolde.php
 * Retourne le solde (crédits) d'un utilisateur via son id.
 * Appelé avec un paramètre GET : /Get/GetSolde.php?idUser=5
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

require_once "../../Classes/CUtilisateurs.php";

// Récupération du paramètre passé dans l'URL via $_GET
// Différent des autres routes qui utilisent le body JSON
$idUser = $_GET['idUser'];

// Appel de la méthode métier qui fait un SELECT du solde par id
$solde = CUtilisateurs::getInstance()->getSoldeById($idUser);

if (!$solde) {
    http_response_code(404);
    echo json_encode(["error" => "Solde non trouvé"]);
    exit;
}

echo json_encode($solde, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>