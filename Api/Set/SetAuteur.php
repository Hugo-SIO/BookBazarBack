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

require_once "../../Classes/CAuteurs.php";
require_once "../auth.php";

getAuthUser();

$idAuteur = $_POST['idAuteur'] ?? null;

if (!$idAuteur) {
    echo json_encode(["success" => false, "message" => "idAuteur manquant"]);
    exit;
}

// Récupérer uniquement les champs présents dans la requête

 $nomAuteur = $_POST['nomAuteur'];

if ($nomAuteur === "") {
    echo json_encode(["success" => false, "message" => "Aucun champ à mettre à jour"]);
    exit;
}

// Construire le SQL dynamiquement
CAuteurs::getInstance()->setAuteur($nomAuteur, $idAuteur);

echo json_encode(["success" => true, "message" => "Auteur mis à jour avec succès"]);