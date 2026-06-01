<?php
/**
 * Contrôleur REST – POST /Set/SetCategorie.php
 * Met à jour le nom d'une catégorie existante en BDD.
 * Route protégée : nécessite un token JWT valide.
 */

// Origines autorisées (CORS)
$allowedOrigins = [
    "http://localhost:5173",
    "https://bookbazar.hugoal.fr"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../../Classes/CCategories.php";
require_once "../auth.php";

/**
 * Vérification du JWT.
 * Le header "Authorization: Bearer <token>" est lu par auth.php.
 * Si invalide → 401 et arrêt immédiat, la BDD n'est pas touchée.
 */
getAuthUser();

// Récupération de l'id depuis le corps FormData (envoyé par React via fetch + FormData)
$idCategorie = $_POST['idCategorie'] ?? null;

// Validation : sans l'id, impossible de savoir quelle ligne modifier
if (!$idCategorie) {
    echo json_encode(["success" => false, "message" => "idCategorie manquant"]);
    exit;
}

/**
 * Récupération du nouveau nom.
 * Côté React, on n'envoie ce champ que s'il a été modifié
 * (comparaison avec la valeur originale avant soumission).
 */
$nomCategorie = $_POST['nomCategorie'];

// Sécurité : refus d'une mise à jour avec un nom vide
if ($nomCategorie === "") {
    echo json_encode(["success" => false, "message" => "Aucun champ à mettre à jour"]);
    exit;
}

/**
 * Appel de la méthode métier via le Singleton.
 * → exécute UPDATE categorie SET nomCategorie = ? WHERE idCategorie = ?
 * Requête préparée avec paramètres nommés → protection injection SQL.
 */
CCategories::getInstance()->setCategorie($nomCategorie, $idCategorie);

echo json_encode(["success" => true, "message" => "Catégorie mis à jour avec succès"]);
?>