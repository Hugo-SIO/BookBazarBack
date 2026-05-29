<?php
    session_start();
    $allowed_origins = [
        "http://localhost:5173",
        "https://site.bookbazar.local"
    ];

    if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    }
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    require_once __DIR__ . '/../../Classes/CAuteurs.php';
    require_once "../auth.php";

    getAuthUser();
    $data = json_decode(file_get_contents("php://input"), true);

    $nomAuteur = $data['nomAuteur'];

    $auteurPresent = CAuteurs::getInstance()->auteurPresent($nomAuteur);

    if(!$auteurPresent){
        CAuteurs::getInstance()->ajouterAuteur($nomAuteur);
    
        http_response_code(201);
        echo json_encode([
                "message" => "Auteur créé avec succès",
        ]);
    }else{
        http_response_code(401);
        echo json_encode(["message" => "Auteur déjà présent"]);
    }
?>