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
    
    require_once "../../Classes/CLivres.php";
    
        // Vérifier que le paramètre existe et est valide
    if (!isset($_GET['idAnnonce']) || !is_numeric($_GET['idAnnonce'])) {
        http_response_code(400);
        echo json_encode(["error" => "idAnnonce manquant ou invalide"]);
        exit;
    }

    $idAnnonce = (int) $_GET['idAnnonce']; // cast en int pour sécuriser

    $livre = CLivres::getInstance()->getLivreById($idAnnonce);

    if (!$livre) {
        http_response_code(404);
        echo json_encode(["error" => "Livre non trouvé"]);
        exit;
    }

    echo json_encode($livre, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>