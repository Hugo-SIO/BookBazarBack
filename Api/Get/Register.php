<?php
    ini_set('display_errors', 1);
error_reporting(E_ALL);
    session_start();
    $allowed_origins = [
        "http://localhost:5173",
        "https://bookbazar.hugoal.fr"
    ];

    if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
    }
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    require_once '../../Classes/CUtilisateurs.php';

    $data = json_decode(file_get_contents("php://input"), true);

    $nom = $data['nom'];
    $prenom = $data['prenom'];
    $adresseMail = $data['adresseMail'];
    $nomUtilisateur = $data['nomUtilisateur'];
    $motDePasse = $data['motDePasse'];
    $idRole = 2;

    $utilisateurPresent = CUtilisateurs::getInstance()->utilisateurPresent($nomUtilisateur,$adresseMail);

    if(!$utilisateurPresent){
        CUtilisateurs::getInstance()->creerUtilisateur($nom, $prenom, $nomUtilisateur, $adresseMail, $motDePasse, $idRole, 0);
        $_SESSION['user'] = [
                    'nomUtilisateur' => $nomUtilisateur,
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'role' => $idRole
        ];
        http_response_code(201);
        echo json_encode([
                "message" => "Utilisateur créé avec succès",
                "user" => $_SESSION['user']
        ]);
    }else{
        http_response_code(401);
        echo json_encode(["message" => "Utilisateur déjà inscrit"]);
    }
?>