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
    
    require_once "../../Classes/CRoles.php";

    $roles = CRoles::getInstance()->getRole();

    echo json_encode($roles, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>