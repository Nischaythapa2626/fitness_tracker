<?php

session_start();

require_once "../config/database.php";

if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";

/* Get exercises from Exercise Library */
$sql = "SELECT id, name, category
        FROM exercises
        ORDER BY category, name";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Failed to load exercises.");
}

/* Save workout */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $date = $_POST['date'];
    $exercise_id = (int) $_POST['exercise_id'];
    $sets = (int) $_POST['sets'];
    $reps = (int) $_POST['reps'];
    $duration = (int) $_POST['duration'];
    $notes = trim($_POST['notes']);

    /* Check required fields */
    if (empty($date)) {

        $error = "Please select a date.";

    } elseif ($exercise_id <= 0) {

        $error = "Please select an exercise.";

    } else {

        /* Create workout */
        $sql = "INSERT INTO workouts
                (user_id, date, notes)
                VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iss",
            $user_id,
            $date,
            $notes
        );

        if (mysqli_stmt_execute($stmt)) {

            $workout_id = mysqli_insert_id($conn);

            mysqli_stmt_close($stmt);

            /* Save workout exercise details */
            $sql = "INSERT INTO workout_exercises
                    (workout_id, exercise_id, sets, reps, duration)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "iiiii",
                $workout_id,
                $exercise_id,
                $sets,
                $reps,
                $duration
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: index.php");
                exit();

            } else {

                $error = "Failed to save exercise details.";
            }

        } else {

            $error = "Failed to create workout.";
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Workout - Exercise Tracker</title>

    <link rel="stylesheet"
          href="../css/style.css">

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->

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

        <a href="../user/dashboard.php"
           class="nav-link">

            <span class="nav-icon">⌂</span>
            Dashboard

        </a>

        <a href="index.php"
           class="nav-link">

            <span class="nav-icon">▣</span>
            My Workouts

        </a>

        <a href="create.php"
           class="nav-link active">

            <span class="nav-icon">＋</span>
            Create Workout

        </a>

        <a href="../exercises/index.php"
           class="nav-link">

            <span class="nav-icon">▤</span>
            Exercise Library

        </a>

        <a href="../exercises/create.php"
           class="nav-link">

            <span class="nav-icon">＋</span>
            Add Exercise

        </a>

        <a href="../auth/logout.php"
           class="nav-link logout">

            <span class="nav-icon">↪</span>
            Logout

        </a>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="top-header">

            <div class="welcome">

                <h2>Create Workout</h2>

                <p>
                    Record your exercise session
                </p>

            </div>

            <div class="date-box">

                <?php echo date("D, d M Y"); ?>

            </div>

        </div>


        <!-- FORM -->

        <div class="form-card">

            <h2>
                NEW WORKOUT
            </h2>

            <p>
                Select an exercise from your Exercise Library
                and enter your workout details.
            </p>


            <?php if ($error != ""): ?>

                <div class="error-message">

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- DATE -->

                <div class="form-group">

                    <label>
                        Workout Date
                    </label>

                    <input
                        type="date"
                        name="date"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>


                <!-- EXERCISE -->

                <div class="form-group">

                    <label>
                        Exercise
                    </label>

                    <select name="exercise_id" required>

                        <option value="">
                            Select an exercise
                        </option>

                        <?php while ($exercise = mysqli_fetch_assoc($result)): ?>

                            <option
                                value="<?php echo $exercise['id']; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $exercise['name']
                                );
                                ?>

                                -
                                <?php
                                echo htmlspecialchars(
                                    $exercise['category']
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <!-- SETS -->

                <div class="form-group">

                    <label>
                        Sets
                    </label>

                    <input
                        type="number"
                        name="sets"
                        value="0"
                        min="0"
                    >

                </div>


                <!-- REPS -->

                <div class="form-group">

                    <label>
                        Reps
                    </label>

                    <input
                        type="number"
                        name="reps"
                        value="0"
                        min="0"
                    >

                </div>


                <!-- DURATION -->

                <div class="form-group">

                    <label>
                        Duration (minutes)
                    </label>

                    <input
                        type="number"
                        name="duration"
                        value="0"
                        min="0"
                    >

                </div>


                <!-- NOTES -->

                <div class="form-group">

                    <label>
                        Notes
                    </label>

                    <textarea
                        name="notes"
                        rows="4"
                        placeholder="Example: Heavy workout, morning session..."
                    ></textarea>

                </div>


                <!-- BUTTONS -->

                <div class="form-buttons">

                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        SAVE WORKOUT
                    </button>

                    <a
                        href="index.php"
                        class="btn-secondary"
                    >
                        CANCEL
                    </a>

                </div>

            </form>

        </div>

    </main>

</div>

</body>

</html>