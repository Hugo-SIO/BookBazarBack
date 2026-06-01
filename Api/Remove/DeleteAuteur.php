<?php
/**
 * Contrôleur REST – DELETE /Remove/DeleteAuteur.php
 * Supprime un auteur de la BDD à partir de son id.
 * Nécessite un token JWT valide (route protégée).
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
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS"); // DELETE explicitement autorisé
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../auth.php";

/**
 * Vérification du JWT avant toute action destructive.
 * Une suppression sans authentification serait une faille
 * de sécurité critique → on vérifie en premier.
 */
getAuthUser();

/**
 * Lecture du corps JSON de la requête DELETE.
 * React envoie : { "idAuteur": 3 }
 * Comme pour le POST JSON, on passe par php://input
 * car $_POST ne lit pas le body d'une requête DELETE.
 */
$data = json_decode(file_get_contents("php://input"), true);

$idAuteur = $data['idAuteur'] ?? null;

require_once '../../Classes/CAuteurs.php';

/**
 * Appel de la méthode métier deleteAuteur() via le Singleton.
 * → exécute DELETE FROM auteur WHERE idAuteur = :idAuteur
 * Les paramètres nommés PDO empêchent toute injection SQL.
 */
CAuteurs::getInstance()->deleteAuteur($idAuteur);

// 200 OK avec message de confirmation
echo json_encode(["message" => "Auteur supprimé avec succès"]);
?>