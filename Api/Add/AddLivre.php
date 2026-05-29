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
require_once "../../Classes/CCategories.php";
require_once "../../Classes/CAuteurs.php";
require_once "../auth.php";

// ─────────────────────────────
// DATA FORM
// ─────────────────────────────
$user = getAuthUser();
$idVendeur = $user['id'];
$titreLivre = $_POST['titreLivre'] ?? null;
$anneeParution = $_POST['annéeParution'] ?? null;
$idCategorie = $_POST['idCategorie'] ?? null;
$idAuteur = $_POST['idAuteur'] ?? null;
$desc = $_POST['desc'] ?? null;
$prix = $_POST['prix'] ?? null;

$newCategorie = $_POST['newCategorie'] ?? "";
$newAuteur = $_POST['newAuteur'] ?? "";

// ─────────────────────────────
// CREATE CATEGORY / AUTHOR IF NEEDED
// ─────────────────────────────
if (!empty($newCategorie)) {
    $categoriePresente = CCategories::getInstance()->categoriePresent($newCategorie);
    if ($categoriePresente) {
        // Elle existe déjà → récupérer son ID
        $idCategorie = CCategories::getInstance()->getIdByNom($newCategorie);
    } else {
        // Elle n'existe pas → la créer et récupérer l'ID
        $idCategorie = CCategories::getInstance()->ajouterCategorie($newCategorie);
    }
}

if (!empty($newAuteur)) {
    $auteurPresent = CAuteurs::getInstance()->auteurPresent($newAuteur);
    if ($auteurPresent) {
        // Il existe déjà → récupérer son ID
        $idAuteur = CAuteurs::getInstance()->getIdByNom($newAuteur);
    } else {
        // Il n'existe pas → le créer et récupérer l'ID
        $idAuteur = CAuteurs::getInstance()->ajouterAuteur($newAuteur);
    }
}

// ─────────────────────────────
// IMAGE UPLOAD (NEW VERSION)
// ─────────────────────────────
$imagePath = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

    $uploadDir = __DIR__ . "/../../uploads/livres/";
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ["image/jpeg", "image/png", "image/webp"];

    if (!in_array($_FILES['image']['type'], $allowedTypes)) {
        echo json_encode(["error" => "Format image non supporté"]);
        exit;
    }

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

    $fileName = time() . "_" . uniqid() . "." . $ext;

    $fullPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $fullPath)) {
        $imagePath = "uploads/livres/" . $fileName;
    } else {
        echo json_encode(["error" => "Erreur upload image"]);
        exit;
    }
}

// ─────────────────────────────
// INSERT DB
// ─────────────────────────────
$livre = CLivres::getInstance()->ajouterLivre(
    $idVendeur,
    $titreLivre,
    $anneeParution,
    $idCategorie,
    $idAuteur,
    $desc,
    $prix,
    $imagePath
);

// ─────────────────────────────
// RESPONSE
// ─────────────────────────────
echo json_encode([
    "success" => true,
    "message" => "Livre ajouté avec succès",
    "image" => $imagePath
]);