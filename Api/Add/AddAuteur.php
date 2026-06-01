<?php
/**
 * Contrôleur REST – POST /Add/AddAuteur.php
 * Crée un nouvel auteur en BDD après avoir vérifié
 * qu'il n'existe pas déjà (évite les doublons).
 * Nécessite un token JWT valide (route protégée).
 */

session_start(); // Démarre la session PHP (utile si auth.php utilise les sessions)

// Origines autorisées (CORS)
$allowed_origins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Gestion du preflight CORS (requête OPTIONS automatique du navigateur)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../Classes/CAuteurs.php';
require_once "../auth.php";

/**
 * Vérification du JWT.
 * Le token est extrait du header "Authorization: Bearer <token>"
 * par auth.php. Si invalide ou absent → 401 et arrêt.
 */
getAuthUser();

/**
 * Lecture du corps JSON de la requête.
 * React envoie : { "nomAuteur": "Victor Hugo" }
 * php://input permet de lire le flux brut (pas disponible via $_POST
 * quand le Content-Type est application/json).
 */
$data = json_decode(file_get_contents("php://input"), true);

$nomAuteur = $data['nomAuteur'];

/**
 * Vérifie si un auteur avec ce nom existe déjà en BDD.
 * La méthode auteurPresent() fait un SELECT COUNT(*) → retourne un booléen.
 * On utilise le Singleton pour ne pas recréer une connexion BDD.
 */
$auteurPresent = CAuteurs::getInstance()->auteurPresent($nomAuteur);

if (!$auteurPresent) {
    // L'auteur n'existe pas → on l'insère en BDD
    CAuteurs::getInstance()->ajouterAuteur($nomAuteur);

    // 201 Created : code HTTP standard pour une ressource créée avec succès
    http_response_code(201);
    echo json_encode(["message" => "Auteur créé avec succès"]);
} else {
    // L'auteur existe déjà → on refuse la création pour éviter un doublon
    // 409 Conflict serait plus précis sémantiquement, mais 401 fonctionne aussi
    http_response_code(401);
    echo json_encode(["message" => "Auteur déjà présent"]);
}
?>