<?php
 $allowed_origins = [
        "http://localhost:5173",
        "https://bookbazar.hugoal.fr"
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

    $idAnnonce = $data['idAnnonce'] ?? null;

    if (!$idUtilisateur) {
        http_response_code(400);
        echo json_encode(["error" => "idUtilisateur manquant"]);
        exit;
    }

    require_once "../../Classes/CLivres.php";

    $acheteur = CLivres::getInstance()->getAcheteur($idAnnonce);

    echo json_encode($acheteur, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

