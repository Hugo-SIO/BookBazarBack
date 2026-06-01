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
    
    require_once "../../Classes/CUtilisateurs.php";
    

    $idUser = $_GET['idUser']; 

    $solde = CUtilisateurs::getInstance()->getSoldeById($idUser);

    if (!$solde) {
        http_response_code(404);
        echo json_encode(["error" => "Solde non trouvé"]);
        exit;
    }

    echo json_encode($solde, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>