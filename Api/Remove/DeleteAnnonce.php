<?php
/**
 * Contrôleur REST – DELETE /Remove/DeleteAnnonce.php
 * Supprime une annonce (livre) par son id.
 * Route protégée : nécessite un token JWT valide.
 *
 * Particularité : la suppression en BDD est précédée
 * d'une suppression du fichier image sur le serveur
 * (logique gérée dans CLivres::deleteAnnonce()).
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

require_once "../auth.php";

/**
 * Vérification du JWT avant toute suppression.
 * Sans cette vérification, n'importe qui pourrait
 * supprimer une annonce avec une simple requête HTTP.
 */
getAuthUser();

/**
 * Lecture du corps JSON de la requête.
 * React envoie : { "idAnnonce": 12 }
 * php://input est nécessaire car $_POST est vide
 * pour les requêtes avec body JSON (Content-Type: application/json).
 */
$data      = json_decode(file_get_contents("php://input"), true);
$idAnnonce = $data['idAnnonce'] ?? null;

require_once '../../Classes/CLivres.php';

/**
 * Appel de la méthode métier via le Singleton CLivres.
 * deleteAnnonce() effectue deux opérations dans l'ordre :
 *   1. SELECT image → récupère le chemin du fichier image
 *   2. unlink()     → supprime le fichier sur le disque
 *   3. DELETE FROM livre → supprime la ligne en BDD
 * Cet ordre est important : si on supprimait d'abord la ligne BDD,
 * on perdrait le chemin de l'image et le fichier resterait orphelin sur le serveur.
 */
CLivres::getInstance()->deleteAnnonce($idAnnonce);

echo json_encode(["message" => "Annonce supprimée avec succès"]);
?>