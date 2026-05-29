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

require_once "../../Classes/CUtilisateurs.php";
require_once "../auth.php";

getAuthUser();
$idUser = $_POST['idUser'] ?? null;
if (!$idUser) {
    echo json_encode(["success" => false, "message" => "idUser manquant"]);
    exit;
}

// Récupérer uniquement les champs présents dans la requête
$fields = [];

if (isset($_POST['nom']))    $fields['nom']      = $_POST['nom'];
if (isset($_POST['prenom'])) $fields['prenom'] = $_POST['prenom'];
if (isset($_POST['pseudo']))   $fields['nomUtilisateur']   = $_POST['pseudo'];
if (isset($_POST['mail']))      $fields['adresseMail']      = $_POST['mail'];
if (isset($_POST['roleId']))          $fields['idRole']          = $_POST['roleId'];
if (isset($_POST['solde'])) $fields['solde'] = $_POST['solde'];

if (empty($fields)) {
    echo json_encode(["success" => false, "message" => "Aucun champ à mettre à jour"]);
    exit;
}

// Construire le SQL dynamiquement
CUtilisateurs::getInstance()->setUtilisateur($fields, (int)$idUser);

echo json_encode(["success" => true, "message" => "Utilisateur mis à jour avec succès"]);