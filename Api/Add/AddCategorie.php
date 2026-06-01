<?php
/**
 * Contrôleur REST – POST /Add/AddCategorie.php
 * Crée une nouvelle catégorie en BDD après vérification
 * de l'absence de doublon.
 * Route protégée : nécessite un token JWT valide.
 */

session_start(); // Démarre la session PHP (utilisée éventuellement par auth.php)

// Origines autorisées pour les appels cross-origin (CORS)
$allowed_origins = [
    "http://localhost:5173",        // React en développement local
    "https://bookbazar.hugoal.fr"  // Frontend en production
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
header("Access-Control-Allow-Credentials: true"); // Autorise l'envoi du token dans les headers
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Preflight CORS : le navigateur envoie une requête OPTIONS avant le vrai POST
// pour vérifier que le serveur accepte la requête cross-origin
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../Classes/CCategories.php';
require_once "../auth.php";

/**
 * Vérification du JWT envoyé dans le header "Authorization: Bearer <token>".
 * Si absent ou invalide → auth.php renvoie 401 et stoppe l'exécution.
 * On vérifie en premier, avant de toucher aux données.
 */
getAuthUser();

/**
 * Lecture du corps JSON de la requête.
 * React envoie : { "nomCategorie": "Science-Fiction" }
 * On utilise php://input car $_POST ne lit pas le JSON brut.
 */
$data         = json_decode(file_get_contents("php://input"), true);
$nomCategorie = $data['nomCategorie'];

/**
 * Vérifie si une catégorie avec ce nom existe déjà.
 * categoriePresent() fait un SELECT COUNT(*) → retourne un booléen.
 */
$categoriePresent = CCategories::getInstance()->categoriePresent($nomCategorie);

if (!$categoriePresent) {
    // Catégorie inexistante → on l'insère en BDD
    CCategories::getInstance()->ajouterCategorie($nomCategorie);

    // 201 Created : code HTTP standard pour une ressource créée avec succès
    http_response_code(201);
    echo json_encode(["message" => "Catégorie créé avec succès"]);
} else {
    // Doublon détecté → on refuse l'insertion
    // (409 Conflict serait plus précis sémantiquement qu'un 401)
    http_response_code(401);
    echo json_encode(["message" => "Catégorie déjà présent"]);
}
?>