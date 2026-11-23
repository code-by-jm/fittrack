<?php

session_start();
header("Content-Type: application/json");

require_once "../config/database.php";

// Authentication
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Please login first"]);
    exit;
}

$userId = $_SESSION["user_id"];
$method = $_SERVER["REQUEST_METHOD"];

function calculateTargets($data) {

    $weight = (float)$data["weight"];
    $height = (float)$data["height"];
    $age = (int)$data["age"];

    $factor = [
        "sedentary" => 1.2,
        "light" => 1.375,
        "moderate" => 1.55,
        "very_active" => 1.725
    ];

    $activity = $factor[$data["activity_level"]] ?? 1.2;

    $bmr = ($data["sex"] === "male")
        ? 10 * $weight + 6.25 * $height - 5 * $age + 5
        : 10 * $weight + 6.25 * $height - 5 * $age - 161;

    $tdee = $bmr * $activity;

    $goal = $data["fitness_goal"];

    $calories = $goal === "muscle_gain"
        ? $tdee + 300
        : ($goal === "weight_loss" ? $tdee - 300 : $tdee);

    $proteinFactor = $goal === "maintenance" ? 1.4 : 1.8;

    return [
        "bmr" => round($bmr),
        "tdee" => round($tdee),
        "daily_calories" => round($calories),
        "daily_protein" => round($weight * $proteinFactor, 1)
    ];
}

if ($method === "GET") {

    $stmt = $pdo->prepare(
        "SELECT * FROM profiles WHERE user_id = ? LIMIT 1"
    );

    $stmt->execute([$userId]);

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        echo json_encode([
            "success" => true,
            "profile" => null,
            "has_profile" => false
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "profile" => $profile,
        "targets" => calculateTargets($profile),
        "has_profile" => true
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
        echo json_encode(["error" => "Invalid JSON"]);
        exit;
    }

    $name = trim($data["name"] ?? "");
    $age = (int)($data["age"] ?? 0);
    $sex = $data["sex"] ?? "";
    $height = (float)($data["height"] ?? 0);
    $weight = (float)($data["weight"] ?? 0);
    $activity = $data["activity_level"] ?? "";
    $goal = $data["fitness_goal"] ?? "";
    $days = (int)($data["workout_days"] ?? 3);

    if (
        $name === "" ||
        $age < 18 || $age > 100 ||
        !in_array($sex, ["male", "female"], true) ||
        $height < 100 || $height > 250 ||
        $weight < 30 || $weight > 300 ||
        !in_array($activity, [
            "sedentary", "light", "moderate", "very_active"
        ], true) ||
        !in_array($goal, [
            "muscle_gain", "weight_loss", "maintenance"
        ], true) ||
        $days < 1 || $days > 7
    ) {
        http_response_code(400);
        echo json_encode([
            "error" => "Invalid profile information"
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT id FROM profiles WHERE user_id = ? LIMIT 1"
    );

    $stmt->execute([$userId]);

    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {

        $stmt = $pdo->prepare(
            "UPDATE profiles SET
                name = ?,
                age = ?,
                sex = ?,
                height = ?,
                weight = ?,
                activity_level = ?,
                fitness_goal = ?,
                workout_days = ?
             WHERE user_id = ?"
        );

        $stmt->execute([
            $name,
            $age,
            $sex,
            $height,
            $weight,
            $activity,
            $goal,
            $days,
            $userId
        ]);

    } else {

        $stmt = $pdo->prepare(
            "INSERT INTO profiles
            (
                user_id,
                name,
                age,
                sex,
                height,
                weight,
                activity_level,
                fitness_goal,
                workout_days
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $userId,
            $name,
            $age,
            $sex,
            $height,
            $weight,
            $activity,
            $goal,
            $days
        ]);
    }

    $profileData = [
        "weight" => $weight,
        "height" => $height,
        "age" => $age,
        "sex" => $sex,
        "activity_level" => $activity,
        "fitness_goal" => $goal
    ];

    echo json_encode([
        "success" => true,
        "message" => "Fitness profile saved successfully!",
        "targets" => calculateTargets($profileData)
    ]);

    exit;
}

http_response_code(405);

echo json_encode([
    "error" => "Method not allowed"
]);
