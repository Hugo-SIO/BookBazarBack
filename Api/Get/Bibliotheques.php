<?php
    $allowedOrigins = [
        "http://localhost:5173",
        "https://bookbazar.hugoal.fr"
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }  
    
    require_once "../../Classes/CBibliotheques.php";

    $bibli = CBibliotheques::getInstance()->getBibliotheque();

    echo json_encode($bibli, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>