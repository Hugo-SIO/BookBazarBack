<?php
    $allowed_origins = [
        "http://localhost:5173",
        "https://site.bookbazar.local"
    ];

    if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    }
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    $data = json_decode(file_get_contents("php://input"), true);

    $idUtilisateur = $data['idUtilisateur'] ?? null;

    if (!$idUtilisateur) {
        http_response_code(400);
        echo json_encode(["error" => "idUtilisateur manquant"]);
        exit;
    }

    require_once "../../Classes/CLivres.php";

    $annonces = CLivres::getInstance()->getLivres();

    $annonceUtilisateur = [];

    foreach ($annonces as $annonce) {
        if ($annonce->getIdVendeur() == $idUtilisateur) {
            $annonceUtilisateur[] = $annonce;
        }
    }

    if ($annonceUtilisateur) {
        echo json_encode($annonceUtilisateur);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Bibliothèque non trouvée"]);
    }
    
?>