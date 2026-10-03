<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($conn) || !$conn) {
    die("Database connection failed.");
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

$workout_id = (int) $_GET['id'];

$error = "";


/* Get workout information */
$sql = "SELECT
            workouts.id,
            workouts.date,
            workouts.notes,
            workout_exercises.exercise_id,
            workout_exercises.sets,
            workout_exercises.reps,
            workout_exercises.duration
        FROM workouts
        INNER JOIN workout_exercises
        ON workouts.id = workout_exercises.workout_id
        WHERE workouts.id = ?
        AND workouts.user_id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $workout_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {

    mysqli_stmt_close($stmt);

    header("Location: index.php");
    exit();
}

$workout = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* Get all exercises */
$sql = "SELECT id, name, category
        FROM exercises
        ORDER BY category, name";

$exercise_result = mysqli_query($conn, $sql);

if (!$exercise_result) {
    die("Failed to load exercises.");
}


/* Update workout */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $date = $_POST['date'];
    $exercise_id = (int) $_POST['exercise_id'];
    $sets = (int) $_POST['sets'];
    $reps = (int) $_POST['reps'];
    $duration = (int) $_POST['duration'];
    $notes = trim($_POST['notes']);


    /* Validation */

    if (empty($date)) {

        $error = "Please select a date.";

    } elseif ($exercise_id <= 0) {

        $error = "Please select an exercise.";

    } else {

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

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);


            /* Update workout exercise */
            $sql = "UPDATE workout_exercises
                    SET exercise_id = ?,
                        sets = ?,
                        reps = ?,
                        duration = ?
                    WHERE workout_id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "iiiii",
                $exercise_id,
                $sets,
                $reps,
                $duration,
                $workout_id
            );


            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: index.php");
                exit();

            } else {

                $error = "Failed to update exercise details.";

                mysqli_stmt_close($stmt);
            }

        } else {

            $error = "Failed to update workout.";

            mysqli_stmt_close($stmt);
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

    <title>Edit Workout - Exercise Tracker</title>

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
           class="nav-link active">

            <span class="nav-icon">▣</span>
            My Workouts

        </a>


        <a href="create.php"
           class="nav-link">

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

                <h2>
                    Edit Workout
                </h2>

                <p>
                    Update your workout session
                </p>

            </div>


            <div class="date-box">

                <?php echo date("D, d M Y"); ?>

            </div>

        </div>


        <!-- FORM -->

        <div class="form-card">

            <h2>
                EDIT WORKOUT
            </h2>

            <p>
                Update the details of your workout session.
            </p>


            <?php if ($error != ""): ?>

                <div class="error-message">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

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
                        value="<?php
                        echo htmlspecialchars(
                            $workout['date']
                        );
                        ?>"
                        required
                    >

                </div>


                <!-- EXERCISE -->

                <div class="form-group">

                    <label>
                        Exercise
                    </label>

                    <select
                        name="exercise_id"
                        required
                    >

                        <option value="">
                            Select an exercise
                        </option>


                        <?php while ($exercise = mysqli_fetch_assoc($exercise_result)): ?>

                            <option
                                value="<?php echo $exercise['id']; ?>"
                                <?php
                                if (
                                    $exercise['id']
                                    == $workout['exercise_id']
                                ) {
                                    echo "selected";
                                }
                                ?>
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
                        value="<?php
                        echo htmlspecialchars(
                            $workout['sets']
                        );
                        ?>"
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
                        value="<?php
                        echo htmlspecialchars(
                            $workout['reps']
                        );
                        ?>"
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
                        value="<?php
                        echo htmlspecialchars(
                            $workout['duration']
                        );
                        ?>"
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
                        placeholder="Example: Heavy workout, evening session..."
                    ><?php
                    echo htmlspecialchars(
                        $workout['notes']
                    );
                    ?></textarea>

                </div>


                <!-- BUTTONS -->

                <div class="form-buttons">

                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        UPDATE WORKOUT
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