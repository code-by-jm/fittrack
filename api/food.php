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

    $action = $_GET["action"] ?? "foods";
    if ($action === "foods") {

        $stmt = $pdo->query(
            "SELECT * FROM foods ORDER BY food_name ASC"
        );

        echo json_encode(
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );

        exit;
    }

    if ($action === "logs") {

        $stmt = $pdo->prepare(
            "SELECT
                food_logs.id,
                foods.food_name,
                food_logs.quantity,
                foods.calories * food_logs.quantity AS total_calories,
                foods.protein * food_logs.quantity AS total_protein,
                food_logs.log_date
             FROM food_logs
             JOIN foods ON food_logs.food_id = foods.id
             WHERE food_logs.profile_id = ?
             AND food_logs.log_date = CURDATE()
             ORDER BY food_logs.id DESC"
        );

        $stmt->execute([$profileId]);

        echo json_encode(
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );

        exit;
    }

    http_response_code(400);

    echo json_encode([
        "error" => "Invalid action"
    ]);

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

    $foodId = (int)($data["food_id"] ?? 0);
    $quantity = (float)($data["quantity"] ?? 0);

    if ($foodId <= 0 || $quantity <= 0 || $quantity > 100) {
        http_response_code(400);
        echo json_encode([
            "error" => "Invalid food or quantity"
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT id FROM foods WHERE id = ?"
    );

    $stmt->execute([$foodId]);

    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            "error" => "Food not found"
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO food_logs
         (profile_id, food_id, quantity, log_date)
         VALUES (?, ?, ?, CURDATE())"
    );

    $stmt->execute([
        $profileId,
        $foodId,
        $quantity
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Food added to your daily tracker!"
    ]);

    exit;
}

http_response_code(405);

echo json_encode([
    "error" => "Method not allowed"
]);

