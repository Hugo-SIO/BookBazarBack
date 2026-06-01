<?php
/**
 * Contrôleur REST – DELETE /Remove/DeleteUser.php
 * Supprime un utilisateur de la BDD à partir de son id.
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
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS"); // DELETE explicitement listé
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../auth.php";

/**
 * Vérification du JWT avant toute suppression.
 * Un DELETE sans authentification serait une faille critique.
 * On vérifie le token en premier, avant de lire les données.
 */
getAuthUser();

/**
 * Lecture du corps JSON de la requête DELETE.
 * React envoie : { "idUtilisateur": 5 }
 * $_POST est vide pour les requêtes DELETE → on passe par php://input.
 */
$data          = json_decode(file_get_contents("php://input"), true);
$idUtilisateur = $data['idUtilisateur'] ?? null;

require_once '../../Classes/CUtilisateurs.php';

/**
 * Appel de la méthode métier via le Singleton.
 * → exécute DELETE FROM utilisateur WHERE idUtilisateur = :idUtilisateur
 * Paramètre nommé PDO → protection contre les injections SQL.
 */
CUtilisateurs::getInstance()->deleteUtilisateur($idUtilisateur);

echo json_encode(["message" => "Utilisateur supprimé avec succès"]);
?>