<?php
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getAuthUser() {
    $secret_key = "9f3c7a8e4b1c2d5f9a0e6b7c8d4f2a1e9c3b5f7d8e6a1c2b4f9e0d7c8a3b6e1f2";

    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!str_starts_with($authHeader, 'Bearer ')) {
        http_response_code(401);
        echo json_encode(["error" => "Token manquant"]);
        exit;
    }

    $token = substr($authHeader, 7);

    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
        return (array) $decoded->data;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Token invalide ou expiré"]);
        exit;
    }
}