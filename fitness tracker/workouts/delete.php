<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    exit("Database connection error.");
}

/* Check login */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}


$user_id = $_SESSION['user_id'];


/* Check workout ID */

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}


$workout_id = (int)$_GET['id'];


/* Delete workout exercises first */

$sql = "DELETE FROM workout_exercises
        WHERE workout_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $workout_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);


/* Delete workout */

$sql = "DELETE FROM workouts
        WHERE id = ? AND user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $workout_id,
    $user_id
);


if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: index.php");
    exit();

} else {

    echo "Failed to delete workout.";

}

?>