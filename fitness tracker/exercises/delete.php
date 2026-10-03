<?php

session_start();

require_once "../config/database.php";

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    exit('Database connection is not available.');
}

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}


/* Check exercise ID */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$exercise_id = (int) $_GET['id'];


/* Check whether exercise is being used in a workout */
$sql = "SELECT COUNT(*) AS total
        FROM workout_exercises
        WHERE exercise_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $exercise_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$row = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* Do not delete an exercise that has workout records */
if ($row['total'] > 0) {

    echo "
    <script>
        alert('This exercise cannot be deleted because it is already used in a workout.');
        window.location.href = 'index.php';
    </script>
    ";

    exit();
}


/* Delete exercise */
$sql = "DELETE FROM exercises
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $exercise_id
);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: index.php");
    exit();

} else {

    mysqli_stmt_close($stmt);

    echo "Failed to delete exercise.";
}

?>