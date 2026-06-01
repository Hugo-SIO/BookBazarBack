<?php
/**
 * Contrôleur REST – POST /Get/BibliothequeByUser.php
 * Retourne la bibliothèque (livres possédés) d'un utilisateur précis.
 * Utilisé sur la page profil pour afficher les livres achetés.
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

// Lecture du corps JSON : React envoie { "idUtilisateur": 5 }
$data          = json_decode(file_get_contents("php://input"), true);
$idUtilisateur = $data['idUtilisateur'] ?? null;

// Validation : sans l'id, impossible de filtrer
if (!$idUtilisateur) {
    http_response_code(400);
    echo json_encode(["error" => "idUtilisateur manquant"]);
    exit;
}

require_once "../../Classes/CBibliotheques.php";

$bibliotheques = CBibliotheques::getInstance()->getBibliotheque();

// Recherche de la bibliothèque correspondant à l'utilisateur
// On s'arrête dès qu'on trouve (break) → optimisation
$bibliothequeUtilisateur = null;
foreach ($bibliotheques as $bib) {
    if ($bib->getIdUtilisateur() == $idUtilisateur) {
        $bibliothequeUtilisateur = $bib;
        break;
    }
}

if ($bibliothequeUtilisateur) {
    echo json_encode($bibliothequeUtilisateur);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Bibliothèque non trouvée"]);
}
?>