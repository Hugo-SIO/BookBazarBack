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
    header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    } 
    require_once "../auth.php";

    getAuthUser();

    $data = json_decode(file_get_contents("php://input"), true);

    $idAuteur = $data['idAuteur'] ?? null;

    require_once '../../Classes/CAuteurs.php';

    CAuteurs::getInstance()->deleteAuteur($idAuteur);

    echo json_encode(["message" => "Auteur supprimé avec succès"]);

?>