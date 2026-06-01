<?php
/**
 * Contrôleur REST – POST /Get/AnnoncesByUser.php
 * Retourne toutes les annonces (livres) publiées
 * par un utilisateur donné (le vendeur).
 * Utilisé par le profil vendeur pour afficher ses livres mis en vente.
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

// Validation : l'id est obligatoire pour filtrer les annonces
if (!$idUtilisateur) {
    http_response_code(400);
    echo json_encode(["error" => "idUtilisateur manquant"]);
    exit;
}

require_once "../../Classes/CLivres.php";

// Récupère tous les livres en mémoire (collection du Singleton)
$annonces = CLivres::getInstance()->getLivres();

// Filtre les livres dont le vendeur correspond à l'id fourni
$annonceUtilisateur = [];
foreach ($annonces as $annonce) {
    if ($annonce->getIdVendeur() == $idUtilisateur) {
        $annonceUtilisateur[] = $annonce;
    }
}

if ($annonceUtilisateur) {
    echo json_encode($annonceUtilisateur);
} else {
    // 404 si aucune annonce trouvée pour cet utilisateur
    http_response_code(404);
    echo json_encode(["error" => "Bibliothèque non trouvée"]);
}
?>