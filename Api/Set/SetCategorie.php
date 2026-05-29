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

require_once "../../Classes/CCategories.php";
require_once "../auth.php";

getAuthUser();

$idCategorie = $_POST['idCategorie'] ?? null;

if (!$idCategorie) {
    echo json_encode(["success" => false, "message" => "idCategorie manquant"]);
    exit;
}

// Récupérer uniquement les champs présents dans la requête

 $nomCategorie = $_POST['nomCategorie'];

if ($nomCategorie === "") {
    echo json_encode(["success" => false, "message" => "Aucun champ à mettre à jour"]);
    exit;
}

// Construire le SQL dynamiquement
CCategories::getInstance()->setCategorie($nomCategorie, $idCategorie);

echo json_encode(["success" => true, "message" => "Catégorie mis à jour avec succès"]);