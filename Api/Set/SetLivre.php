<?php
$allowedOrigins = [
    "http://localhost:5173",
    "https://site.bookbazar.local"
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../../Classes/CLivres.php";
require_once "../auth.php";

getAuthUser();

$idAnnonce = $_POST['idAnnonce'] ?? null;
if (!$idAnnonce) {
    echo json_encode(["success" => false, "message" => "idAnnonce manquant"]);
    exit;
}

// Récupérer uniquement les champs présents dans la requête
$fields = [];

if (isset($_POST['titreLivre']))    $fields['nomLivre']      = $_POST['titreLivre'];
if (isset($_POST['anneeParution'])) $fields['anneeParution'] = $_POST['anneeParution'];
if (isset($_POST['idCategorie']))   $fields['idCategorie']   = $_POST['idCategorie'];
if (isset($_POST['idAuteur']))      $fields['idAuteur']      = $_POST['idAuteur'];
if (isset($_POST['prix']))          $fields['prix']          = $_POST['prix'];
if (isset($_POST['desc']))          $fields['description']   = $_POST['desc'];

// Gestion de l'image si une nouvelle est envoyée
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $allowedTypes = ["image/jpeg", "image/png", "image/webp"];
    if (!in_array($_FILES['image']['type'], $allowedTypes)) {
        echo json_encode(["success" => false, "message" => "Format image non supporté"]);
        exit;
    }

    // Supprimer l'ancienne image
    $existing = CLivres::getInstance()->getLivreById((int)$idAnnonce);
    if (!empty($existing['image'])) {
        $oldPath = __DIR__ . "/../../" . $existing['image'];
        if (file_exists($oldPath)) unlink($oldPath);
    }

    // Sauvegarder la nouvelle
    $uploadDir = "../../uploads/livres/";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $fileName = time() . "_" . uniqid() . "." . $ext;
    $fullPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
        $fields['image'] = "uploads/livres/" . $fileName;
    } else {
        echo json_encode(["success" => false, "message" => "Erreur upload image"]);
        exit;
    }
}


if (empty($fields)) {
    echo json_encode(["success" => false, "message" => "Aucun champ à mettre à jour"]);
    exit;
}

// Construire le SQL dynamiquement
CLivres::getInstance()->setLivre($fields, (int)$idAnnonce);

echo json_encode(["success" => true, "message" => "Livre mis à jour avec succès"]);