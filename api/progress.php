<?php

session_start();
header("Content-Type: application/json");

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "error" => "Please login first"
    ]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];
$stmt = $pdo->prepare(
    "SELECT id FROM profiles
     WHERE user_id = ?
     LIMIT 1"
);

$stmt->execute([$_SESSION["user_id"]]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    http_response_code(404);
    echo json_encode([
        "error" => "Complete your profile first"
    ]);
    exit;
}

$profileId = (int)$profile["id"];

if ($method === "GET") {

    $stmt = $pdo->prepare(
        "SELECT * FROM progress
         WHERE profile_id = ?
         ORDER BY record_date DESC, id DESC"
    );

    $stmt->execute([$profileId]);

    echo json_encode(
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

    exit;
}

if ($method === "POST") {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode([
            "error" => "Invalid JSON"
        ]);
        exit;
    }

    $weight = (float)($data["weight"] ?? 0);

    if ($weight < 30 || $weight > 300) {
        http_response_code(400);
        echo json_encode([
            "error" => "Invalid weight"
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO progress
         (profile_id, weight, record_date)
         VALUES (?, ?, CURDATE())"
    );

    $stmt->execute([
        $profileId,
        $weight
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Weight progress recorded!"
    ]);

    exit;
}
http_response_code(405);

echo json_encode([
    "error" => "Method not allowed"
]);

