<?php
/**
 * Contrôleur REST – POST /Set/SetAuteur.php
 * Met à jour le nom d'un auteur existant en BDD.
 * Nécessite un token JWT valide (route protégée).
 */

// Origines autorisées pour les appels cross-origin (CORS)
$allowedOrigins = [
    "http://localhost:5173",        // Frontend React en développement local
    "https://bookbazar.hugoal.fr"  // Frontend en production
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? ''; // Origine de la requête (envoyée par le navigateur)

// On autorise uniquement les origines connues
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true"); // Autorise l'envoi de cookies/tokens
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Authorization pour le JWT
header("Content-Type: application/json");

// Preflight CORS : le navigateur envoie d'abord une requête OPTIONS
// avant la vraie requête POST pour vérifier les permissions
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../../Classes/CAuteurs.php";
require_once "../auth.php";

/**
 * Vérifie le token JWT envoyé dans le header Authorization.
 * Si le token est absent ou invalide, auth.php renvoie une
 * erreur 401 et coupe l'exécution → la route est protégée.
 */
getAuthUser();

// Récupération de l'id depuis le corps de la requête (FormData)
$idAuteur = $_POST['idAuteur'] ?? null;

// Validation : l'id est obligatoire pour savoir quel auteur modifier
if (!$idAuteur) {
    echo json_encode(["success" => false, "message" => "idAuteur manquant"]);
    exit;
}

// Récupération du nouveau nom (envoyé uniquement s'il a changé côté React)
$nomAuteur = $_POST['nomAuteur'];

// Sécurité : on refuse une mise à jour avec un nom vide
if ($nomAuteur === "") {
    echo json_encode(["success" => false, "message" => "Aucun champ à mettre à jour"]);
    exit;
}

// Appel de la méthode métier via le Singleton CAuteurs
// → exécute un UPDATE en BDD avec des paramètres nommés (protection injection SQL)
CAuteurs::getInstance()->setAuteur($nomAuteur, $idAuteur);

echo json_encode(["success" => true, "message" => "Auteur mis à jour avec succès"]);
?>