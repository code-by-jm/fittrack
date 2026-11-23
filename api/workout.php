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

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([
        "error" => "GET method only"
    ]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT fitness_goal, workout_days
     FROM profiles
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

$goal = $profile["fitness_goal"];
$days = (int)$profile["workout_days"];

$exerciseLibrary = [

    "muscle_gain" => [
        [
            "name" => "Push-ups",
            "sets" => 3,
            "reps" => "8-12",
            "rest" => "60 seconds"
        ],
        [
            "name" => "Bodyweight Squats",
            "sets" => 3,
            "reps" => "12-15",
            "rest" => "60 seconds"
        ],
        [
            "name" => "Backpack Rows",
            "sets" => 3,
            "reps" => "10-12",
            "rest" => "60 seconds"
        ],
        [
            "name" => "Glute Bridges",
            "sets" => 3,
            "reps" => "12-15",
            "rest" => "45 seconds"
        ],
        [
            "name" => "Plank",
            "sets" => 3,
            "reps" => "20-30 seconds",
            "rest" => "45 seconds"
        ]
    ],

    "weight_loss" => [
        [
            "name" => "Marching in Place",
            "sets" => 3,
            "reps" => "2 minutes",
            "rest" => "30 seconds"
        ],
        [
            "name" => "Bodyweight Squats",
            "sets" => 3,
            "reps" => "12-15",
            "rest" => "45 seconds"
        ],
        [
            "name" => "Step Jacks",
            "sets" => 3,
            "reps" => "1 minute",
            "rest" => "30 seconds"
        ],
        [
            "name" => "Wall Push-ups",
            "sets" => 3,
            "reps" => "10-12",
            "rest" => "45 seconds"
        ],
        [
            "name" => "Standing Knee Raises",
            "sets" => 3,
            "reps" => "15 each side",
            "rest" => "30 seconds"
        ]
    ],

    "maintenance" => [
        [
            "name" => "Bodyweight Squats",
            "sets" => 3,
            "reps" => "12",
            "rest" => "45 seconds"
        ],
        [
            "name" => "Push-ups",
            "sets" => 3,
            "reps" => "8-12",
            "rest" => "60 seconds"
        ],
        [
            "name" => "Bird Dogs",
            "sets" => 3,
            "reps" => "10 each side",
            "rest" => "30 seconds"
        ],
        [
            "name" => "Glute Bridges",
            "sets" => 3,
            "reps" => "12",
            "rest" => "45 seconds"
        ],
        [
            "name" => "Plank",
            "sets" => 3,
            "reps" => "20-30 seconds",
            "rest" => "45 seconds"
        ]
    ]
];

if (!isset($exerciseLibrary[$goal])) {
    http_response_code(400);
    echo json_encode([
        "error" => "Invalid fitness goal"
    ]);
    exit;
}

$exercises = $exerciseLibrary[$goal];

$workoutPlan = [];

for ($day = 1; $day <= $days; $day++) {

    $workoutPlan[] = [
        "day" => "Day " . $day,
        "title" => "Home Workout",
        "warmup" => "5-10 minutes of light movement",
        "exercises" => $exercises,
        "cooldown" => "5 minutes of gentle stretching"
    ];
}

echo json_encode([
    "success" => true,
    "goal" => $goal,
    "workout_days" => $days,
    "plan" => $workoutPlan
]);

