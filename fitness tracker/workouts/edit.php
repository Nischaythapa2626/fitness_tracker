<?php

session_start();

require_once "../config/database.php";

if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = mysqli_connect("localhost", "root", "", "fitness_tracker");

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }
}

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/* Check workout ID */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$workout_id = (int)$_GET['id'];

$error = "";


/* Get workout information */
$sql = "SELECT
            workouts.id AS workout_id,
            workouts.date,
            workouts.notes,

            workout_exercises.exercise_id,
            workout_exercises.sets,
            workout_exercises.reps,
            workout_exercises.duration,

            exercises.name AS exercise_name,
            exercises.category

        FROM workouts

        LEFT JOIN workout_exercises
        ON workouts.id = workout_exercises.workout_id

        LEFT JOIN exercises
        ON workout_exercises.exercise_id = exercises.id

        WHERE workouts.id = ?
        AND workouts.user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("SQL prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $workout_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$workout = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* Check workout */
if (!$workout) {
    die("Workout not found. Workout ID: " . $workout_id);
}


/* Update workout */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $date = trim($_POST['date'] ?? "");
    $exercise_name = trim($_POST['exercise_name'] ?? "");
    $category = trim($_POST['category'] ?? "");
    $sets = (int)($_POST['sets'] ?? 0);
    $reps = (int)($_POST['reps'] ?? 0);
    $duration = (int)($_POST['duration'] ?? 0);
    $notes = trim($_POST['notes'] ?? "");


    /* Validation */
    if (empty($date)) {

        $error = "Please select a date.";

    } elseif (empty($exercise_name)) {

        $error = "Please enter exercise name.";

    } elseif (empty($category)) {

        $error = "Please enter category.";

    } else {


        /* Update exercise */
        if (!empty($workout['exercise_id'])) {

            $sql = "UPDATE exercises
                    SET name = ?, category = ?
                    WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssi",
                $exercise_name,
                $category,
                $workout['exercise_id']
            );

            if (!mysqli_stmt_execute($stmt)) {

                $error = "Failed to update exercise.";

            }

            mysqli_stmt_close($stmt);

        }


        /* Continue only if there is no error */
        if ($error == "") {


            /* Update workout */
            $sql = "UPDATE workouts
                    SET date = ?, notes = ?
                    WHERE id = ?
                    AND user_id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssii",
                $date,
                $notes,
                $workout_id,
                $user_id
            );

            if (!mysqli_stmt_execute($stmt)) {

                $error = "Failed to update workout.";

            }

            mysqli_stmt_close($stmt);
        }


        /* Update workout details */
        if ($error == "" && !empty($workout['exercise_id'])) {

            $sql = "UPDATE workout_exercises
                    SET sets = ?, reps = ?, duration = ?
                    WHERE workout_id = ?
                    AND exercise_id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "iiiii",
                $sets,
                $reps,
                $duration,
                $workout_id,
                $workout['exercise_id']
            );

            if (!mysqli_stmt_execute($stmt)) {

                $error = "Failed to update workout details.";

            }

            mysqli_stmt_close($stmt);
        }


        /* If everything worked */
        if ($error == "") {

            header("Location: index.php");
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Workout - Exercise Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <div class="logo">
            <h1>
                EXERCISE<br>
                <span>TRACKER</span>
            </h1>
            <p>TRAIN • TRACK • IMPROVE</p>
        </div>

        <div class="nav-title">
            MAIN MENU
        </div>

        <a href="../user/dashboard.php" class="nav-link">
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>

        <a href="index.php" class="nav-link active">
            <span class="nav-icon">▣</span>
            My Workouts
        </a>

        <a href="create.php" class="nav-link">
            <span class="nav-icon">＋</span>
            Create Workout
        </a>

        <a href="../exercises/index.php" class="nav-link">
            <span class="nav-icon">▤</span>
            Exercise Library
        </a>

        <a href="../auth/logout.php" class="nav-link logout">
            <span class="nav-icon">↪</span>
            Logout
        </a>

    </aside>

    <main class="main-content">

        <div class="top-header">
            <div class="welcome">
                <h2>Edit Workout</h2>
                <p>Update your exercise details and workout notes.</p>
            </div>

            <div class="date-box">
                <?php echo date("D, d M Y"); ?>
            </div>
        </div>

        <div class="form-card">

            <?php if ($error != ""): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Workout Date</label>
                    <input
                        type="date"
                        name="date"
                        value="<?php echo htmlspecialchars($workout['date']); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Exercise Name</label>
                    <input
                        type="text"
                        name="exercise_name"
                        value="<?php echo htmlspecialchars($workout['exercise_name'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input
                        type="text"
                        name="category"
                        value="<?php echo htmlspecialchars($workout['category'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Sets</label>
                    <input
                        type="number"
                        name="sets"
                        value="<?php echo htmlspecialchars((string)($workout['sets'] ?? 0)); ?>"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label>Reps</label>
                    <input
                        type="number"
                        name="reps"
                        value="<?php echo htmlspecialchars((string)($workout['reps'] ?? 0)); ?>"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label>Duration (minutes)</label>
                    <input
                        type="number"
                        name="duration"
                        value="<?php echo htmlspecialchars((string)($workout['duration'] ?? 0)); ?>"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="5"><?php echo htmlspecialchars($workout['notes'] ?? ''); ?></textarea>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-primary">
                        UPDATE WORKOUT
                    </button>

                    <a href="index.php" class="btn-secondary">
                        CANCEL
                    </a>
                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>