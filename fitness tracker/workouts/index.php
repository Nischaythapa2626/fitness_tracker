<?php

session_start();

require_once "../config/database.php";

if (!isset($conn) || !$conn) {
    $conn = mysqli_connect('localhost', 'root', '', 'fitness_tracker');

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }
}

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


/* ==============================
   GET USER'S WORKOUTS
   ============================== */

$sql = "SELECT
            workouts.id AS workout_id,
            workouts.date,
            workouts.notes,
            exercises.name AS exercise_name,
            exercises.category,
            workout_exercises.sets,
            workout_exercises.reps,
            workout_exercises.duration

        FROM workouts

        LEFT JOIN workout_exercises
        ON workouts.id = workout_exercises.workout_id

        LEFT JOIN exercises
        ON workout_exercises.exercise_id = exercises.id

        WHERE workouts.user_id = ?

        ORDER BY workouts.date DESC";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("SQL prepare failed: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Workouts - Exercise Tracker</title>

    <link rel="stylesheet" href="../css/style.css">


    <style>

        /* ==============================
           PAGE HEADER
           ============================== */

        .page-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;

        }


        .page-title h2 {

            font-size: 24px;

            margin-bottom: 7px;

        }


        .page-title p {

            color: #777d85;

            font-size: 12px;

        }


        /* ==============================
           ADD WORKOUT BUTTON
           ============================== */

        .add-workout-button {

            display: inline-block;

            background: #e3262e;

            color: white;

            text-decoration: none;

            padding: 12px 17px;

            border-radius: 6px;

            font-size: 11px;

            font-weight: bold;

            transition: 0.2s;

        }


        .add-workout-button:hover {

            background: #c91d25;

            transform: translateY(-1px);

        }


        /* ==============================
           WORKOUT TABLE CARD
           ============================== */

        .workout-table-card {

            background: #15181c;

            border: 1px solid #292d32;

            border-radius: 10px;

            overflow: hidden;

        }


        .table-top {

            padding: 20px 22px;

            border-bottom: 1px solid #292d32;

        }


        .table-top h3 {

            font-size: 14px;

            margin-bottom: 5px;

        }


        .table-top p {

            color: #777d85;

            font-size: 10px;

        }


        /* ==============================
           TABLE
           ============================== */

        .workout-table-wrapper {

            width: 100%;

            overflow-x: auto;

        }


        .workout-table {

            width: 100%;

            border-collapse: collapse;

            min-width: 850px;

        }


        .workout-table th {

            background: #101316;

            color: #777e86;

            font-size: 10px;

            font-weight: bold;

            text-align: left;

            padding: 14px 16px;

            letter-spacing: 0.5px;

            text-transform: uppercase;

            border-bottom: 1px solid #292d32;

        }


        .workout-table td {

            padding: 16px;

            color: #d2d5d8;

            font-size: 11px;

            border-bottom: 1px solid #24272b;

            vertical-align: middle;

        }


        .workout-table tr:last-child td {

            border-bottom: none;

        }


        .workout-table tbody tr {

            transition: 0.15s;

        }


        .workout-table tbody tr:hover {

            background: #191c20;

        }


        /* ==============================
           DATE
           ============================== */

        .workout-date {

            color: white;

            font-weight: bold;

        }


        /* ==============================
           EXERCISE
           ============================== */

        .exercise-name {

            color: white;

            font-weight: bold;

        }


        .category-badge {

            display: inline-block;

            margin-top: 5px;

            padding: 4px 7px;

            background: #24282d;

            border-radius: 4px;

            color: #9da3aa;

            font-size: 9px;

        }


        /* ==============================
           NUMBERS
           ============================== */

        .workout-number {

            color: #f0f0f0;

            font-weight: bold;

        }


        .duration {

            color: #aeb4bb;

        }


        /* ==============================
           NOTES
           ============================== */

        .notes {

            max-width: 160px;

            color: #969da5;

            line-height: 1.4;

        }


        /* ==============================
           ACTIONS
           ============================== */

        .actions {

            white-space: nowrap;

        }


        .edit-button,
        .delete-button {

            display: inline-block;

            padding: 7px 10px;

            border-radius: 5px;

            text-decoration: none;

            font-size: 10px;

            font-weight: bold;

        }


        .edit-button {

            background: #24282d;

            color: #d7d9dc;

        }


        .edit-button:hover {

            background: #343940;

            color: white;

        }


        .delete-button {

            background: #321316;

            color: #f06b72;

            margin-left: 4px;

        }


        .delete-button:hover {

            background: #e3262e;

            color: white;

        }


        /* ==============================
           EMPTY STATE
           ============================== */

        .empty-state {

            text-align: center;

            padding: 65px 25px;

        }


        .empty-icon {

            font-size: 35px;

            color: #e3262e;

            margin-bottom: 18px;

        }


        .empty-state h3 {

            font-size: 17px;

            margin-bottom: 8px;

        }


        .empty-state p {

            color: #777d85;

            font-size: 11px;

            margin-bottom: 20px;

        }


        /* ==============================
           MOBILE
           ============================== */

        @media (max-width: 700px) {

            .page-header {

                display: block;

            }


            .add-workout-button {

                margin-top: 15px;

            }

        }

    </style>

</head>


<body>


<div class="dashboard">


    <!-- ==============================
         SIDEBAR
         ============================== -->

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


    <!-- ==============================
         MAIN CONTENT
         ============================== -->

    <main class="main-content">


        <!-- PAGE HEADER -->

        <div class="page-header">


            <div class="page-title">

                <h2>
                    My Workouts
                </h2>

                <p>
                    View and manage your recorded exercise sessions.
                </p>

            </div>


            <a href="create.php"
               class="add-workout-button">

                + CREATE WORKOUT

            </a>


        </div>


        <!-- ==============================
             WORKOUT TABLE
             ============================== -->

        <div class="workout-table-card">


            <div class="table-top">

                <h3>
                    Workout History
                </h3>

                <p>
                    Your saved exercise sessions
                </p>

            </div>


            <?php if (mysqli_num_rows($result) > 0): ?>


                <div class="workout-table-wrapper">


                    <table class="workout-table">


                        <thead>

                            <tr>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Exercise
                                </th>

                                <th>
                                    Sets
                                </th>

                                <th>
                                    Reps
                                </th>

                                <th>
                                    Duration
                                </th>

                                <th>
                                    Notes
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($workout = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <!-- DATE -->

                                <td>

                                    <span class="workout-date">

                                        <?php
                                        echo htmlspecialchars(
                                            $workout['date']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- EXERCISE -->

                                <td>

                                    <div class="exercise-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $workout['exercise_name']
                                        );
                                        ?>

                                    </div>


                                    <span class="category-badge">

                                        <?php
                                        echo htmlspecialchars(
                                            $workout['category']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- SETS -->

                                <td>

                                    <span class="workout-number">

                                        <?php
                                        echo htmlspecialchars(
                                            $workout['sets']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- REPS -->

                                <td>

                                    <span class="workout-number">

                                        <?php
                                        echo htmlspecialchars(
                                            $workout['reps']
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- DURATION -->

                                <td>

                                    <span class="duration">

                                        <?php
                                        echo htmlspecialchars(
                                            $workout['duration']
                                        );
                                        ?>

                                        min

                                    </span>

                                </td>


                                <!-- NOTES -->

                                <td>

                                    <div class="notes">

                                        <?php

                                        if (
                                            !empty(
                                                $workout['notes']
                                            )
                                        ) {

                                            echo htmlspecialchars(
                                                $workout['notes']
                                            );

                                        } else {

                                            echo "—";

                                        }

                                        ?>

                                    </div>

                                </td>


                                <!-- ACTIONS -->

                                <td class="actions">


                                    <a
                                        href="edit.php?id=<?php echo $workout['workout_id']; ?>"
                                        class="edit-button"
                                    >

                                        EDIT

                                    </a>


                                    <a
                                        href="delete.php?id=<?php echo $workout['workout_id']; ?>"
                                        class="delete-button"

                                        onclick="return confirm(
                                            'Are you sure you want to delete this workout?'
                                        );"
                                    >

                                        DELETE

                                    </a>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <!-- EMPTY STATE -->

                <div class="empty-state">


                    <div class="empty-icon">
                        ♢
                    </div>


                    <h3>
                        No workouts yet
                    </h3>


                    <p>
                        Start recording your workouts and build your exercise history.
                    </p>


                    <a
                        href="create.php"
                        class="add-workout-button"
                    >

                        CREATE YOUR FIRST WORKOUT

                    </a>


                </div>


            <?php endif; ?>


        </div>


    </main>


</div>


</body>

</html>


<?php

mysqli_stmt_close($stmt);

?>