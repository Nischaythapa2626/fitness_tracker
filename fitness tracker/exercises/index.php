<?php

session_start();

require_once "../config/database.php";

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

/* Get exercises */
$sql = "SELECT * FROM exercises
        ORDER BY category, name";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("SQL prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Exercise Library - Exercise Tracker</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <style>

        .library-card {
            background: #101316;
            border: 1px solid #292d32;
            border-radius: 12px;
            padding: 25px;
            margin-top: 25px;
        }

        .library-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .library-header h2 {
            margin: 0;
            color: #ffffff;
        }

        .add-button {
            background: #ed1c24;
            color: #ffffff;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 700;
        }

        .exercise-table {
            width: 100%;
            border-collapse: collapse;
        }

        .exercise-table th {
            text-align: left;
            padding: 14px;
            color: #8f969d;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #292d32;
        }

        .exercise-table td {
            padding: 15px 14px;
            color: #d8dce0;
            border-bottom: 1px solid #202428;
            font-size: 14px;
        }

        .exercise-name {
            color: #ffffff;
            font-weight: 600;
        }

        .category-badge {
            display: inline-block;
            background: rgba(237, 28, 36, 0.12);
            color: #ff4b52;
            padding: 5px 9px;
            border-radius: 5px;
            font-size: 12px;
        }

        .action-links {
            display: flex;
            gap: 8px;
        }

        .edit-link {
            background: #252a2f;
            color: #ffffff;
            text-decoration: none;
            padding: 7px 11px;
            border-radius: 5px;
            font-size: 12px;
        }

        .delete-link {
            background: rgba(237, 28, 36, 0.12);
            color: #ff4b52;
            text-decoration: none;
            padding: 7px 11px;
            border-radius: 5px;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #8f969d;
        }

        .empty-state h3 {
            color: #ffffff;
            margin-bottom: 8px;
        }

        @media (max-width: 700px) {

            .library-card {
                padding: 15px;
                overflow-x: auto;
            }

            .library-header {
                min-width: 600px;
            }

            .exercise-table {
                min-width: 700px;
            }
        }

    </style>

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

        <a href="../workouts/index.php"
           class="nav-link">

            <span class="nav-icon">▣</span>
            My Workouts

        </a>

        <a href="../workouts/create.php"
           class="nav-link">

            <span class="nav-icon">＋</span>
            Create Workout

        </a>

        <a href="index.php"
           class="nav-link active">

            <span class="nav-icon">▤</span>
            Exercise Library

        </a>

        <a href="create.php"
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

                <h2>Exercise Library</h2>

                <p>
                    Manage exercises available for your workouts
                </p>

            </div>

            <div class="date-box">

                <?php echo date("D, d M Y"); ?>

            </div>

        </div>


        <div class="library-card">

            <div class="library-header">

                <h2>
                    ALL EXERCISES
                </h2>

                <a href="create.php"
                   class="add-button">

                    + ADD EXERCISE

                </a>

            </div>


            <?php if (mysqli_num_rows($result) > 0): ?>

                <table class="exercise-table">

                    <thead>

                        <tr>

                            <th>
                                Exercise
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Description
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($exercise = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td class="exercise-name">

                                <?php
                                echo htmlspecialchars(
                                    $exercise['name']
                                );
                                ?>

                            </td>


                            <td>

                                <span class="category-badge">

                                    <?php
                                    echo htmlspecialchars(
                                        $exercise['category']
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $exercise['description']
                                );
                                ?>

                            </td>


                            <td>

                                <div class="action-links">

                                    <a
                                        href="edit.php?id=<?php echo $exercise['id']; ?>"
                                        class="edit-link"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete.php?id=<?php echo $exercise['id']; ?>"
                                        class="delete-link"
                                        onclick="return confirm('Are you sure you want to delete this exercise?');"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>


            <?php else: ?>

                <div class="empty-state">

                    <h3>
                        No Exercises Yet
                    </h3>

                    <p>
                        Add your first exercise to the Exercise Library.
                    </p>

                    <br>

                    <a
                        href="create.php"
                        class="add-button"
                    >
                        + ADD EXERCISE
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