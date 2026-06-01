<?php
/**
 * Contrôleur REST – POST /Set/SetLivre.php
 * Met à jour un ou plusieurs champs d'un livre existant,
 * et remplace l'image si une nouvelle est envoyée.
 * Route protégée : nécessite un token JWT valide.
 *
 * Utilise FormData côté React (pas JSON) car on peut
 * recevoir à la fois des champs texte ET un fichier image
 * dans la même requête → multipart/form-data obligatoire.
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

require_once "../../Classes/CLivres.php";
require_once "../auth.php";

/**
 * Vérification du JWT.
 * Si le token est absent ou invalide, auth.php
 * renvoie un 401 et stoppe l'exécution ici.
 */
getAuthUser();

// Récupération de l'id de l'annonce à modifier (depuis FormData)
$idAnnonce = $_POST['idAnnonce'] ?? null;

if (!$idAnnonce) {
    echo json_encode(["success" => false, "message" => "idAnnonce manquant"]);
    exit;
}

/**
 * Construction dynamique du tableau $fields.
 * On n'inclut que les champs présents dans $_POST,
 * c'est-à-dire uniquement ceux modifiés côté React
 * (comparaison avec les valeurs originales dans le composant Modal).
 * La correspondance $_POST → colonne SQL est faite ici.
 * Ex: $_POST['titreLivre'] → colonne "nomLivre" en BDD.
 */
$fields = [];
if (isset($_POST['titreLivre']))    $fields['nomLivre']      = $_POST['titreLivre'];
if (isset($_POST['anneeParution'])) $fields['anneeParution'] = $_POST['anneeParution'];
if (isset($_POST['idCategorie']))   $fields['idCategorie']   = $_POST['idCategorie'];
if (isset($_POST['idAuteur']))      $fields['idAuteur']      = $_POST['idAuteur'];
if (isset($_POST['prix']))          $fields['prix']          = $_POST['prix'];
if (isset($_POST['desc']))          $fields['description']   = $_POST['desc'];

/**
 * Gestion de l'image : traitement uniquement si une nouvelle image est envoyée.
 * $_FILES['image']['error'] === 0 signifie "upload réussi sans erreur".
 * Si l'utilisateur n'a pas changé l'image, $_FILES est vide → ce bloc est ignoré.
 */
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    // Vérification du type MIME pour n'accepter que des images
    $allowedTypes = ["image/jpeg", "image/png", "image/webp"];
    if (!in_array($_FILES['image']['type'], $allowedTypes)) {
        echo json_encode(["success" => false, "message" => "Format image non supporté"]);
        exit;
    }

    /**
     * Suppression de l'ancienne image avant d'uploader la nouvelle.
     * getLivreById() récupère le chemin actuel de l'image en BDD.
     * unlink() supprime le fichier du disque → évite les fichiers orphelins.
     */
    $existing = CLivres::getInstance()->getLivreById((int)$idAnnonce);
    if (!empty($existing['image'])) {
        $oldPath = __DIR__ . "/../../" . $existing['image'];
        if (file_exists($oldPath)) unlink($oldPath);
    }

    // Upload de la nouvelle image dans le dossier dédié
    $uploadDir = "../../uploads/livres/";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    // Nom unique généré avec time() + uniqid() → évite les collisions de noms
    $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $fileName = time() . "_" . uniqid() . "." . $ext;
    $fullPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
        // Seul le chemin relatif est stocké en BDD (pas le chemin absolu)
        $fields['image'] = "uploads/livres/" . $fileName;
    } else {
        echo json_encode(["success" => false, "message" => "Erreur upload image"]);
        exit;
    }
}

// Sécurité : refus si aucun champ n'a été envoyé (requête vide inutile)
if (empty($fields)) {
    echo json_encode(["success" => false, "message" => "Aucun champ à mettre à jour"]);
    exit;
}

/**
 * Appel de la méthode métier via le Singleton CLivres.
 * setLivre() construit le SQL dynamiquement avec implode()
 * et des paramètres nommés PDO → protection contre les injections SQL,
 * même avec un SQL généré dynamiquement.
 */
CLivres::getInstance()->setLivre($fields, (int)$idAnnonce);

echo json_encode(["success" => true, "message" => "Livre mis à jour avec succès"]);
?>