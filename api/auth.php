<?php
header("Content-Type: application/json");

session_start();
require_once "../config/database.php";

$method = $_SERVER["REQUEST_METHOD"];

// Check login status
if ($method === "GET") {
    if (!isset($_SESSION["user_id"])) {
        echo json_encode([
            "logged_in" => false
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT id, name, email FROM users WHERE id = ?"
    );
    $stmt->execute([$_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_destroy();
        echo json_encode(["logged_in" => false]);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT id FROM profiles WHERE user_id = ?"
    );
    $stmt->execute([$user["id"]]);

    echo json_encode([
        "logged_in" => true,
        "user" => $user,
        "has_profile" => (bool) $stmt->fetch()
    ]);
    exit;
}

// All other actions use POST
if ($method !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $data["action"] ?? "";

// Register
if ($action === "register") {
    $name = trim($data["name"] ?? "");
    $email = strtolower(trim($data["email"] ?? ""));
    $password = $data["password"] ?? "";

    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($password) < 8) {
        http_response_code(400);
        echo json_encode([
            "error" => "Enter a valid name and email. Password must be at least 8 characters."
        ]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["error" => "Email is already registered."]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
    );
    $stmt->execute([$name, $email, $hashedPassword]);

    session_regenerate_id(true);
    $_SESSION["user_id"] = $pdo->lastInsertId();

    echo json_encode([
        "success" => true,
        "redirect" => "profile.html"
    ]);
    exit;
}

// Login
if ($action === "login") {
    $email = strtolower(trim($data["email"] ?? ""));
    $password = $data["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT id, name, email, password FROM users WHERE email = ?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["password"])) {
        http_response_code(401);
        echo json_encode(["error" => "Incorrect email or password."]);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION["user_id"] = $user["id"];

    $stmt = $pdo->prepare(
        "SELECT id FROM profiles WHERE user_id = ?"
    );
    $stmt->execute([$user["id"]]);

    echo json_encode([
        "success" => true,
        "redirect" => $stmt->fetch()
            ? "index.html"
            : "profile.html"
    ]);
    exit;
}

// Logout
if ($action === "logout") {
    $_SESSION = [];
    session_destroy();

    echo json_encode(["success" => true]);
    exit;
}

http_response_code(400);
echo json_encode(["error" => "Invalid action"]);
