<?php

session_start();

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Exercise Tracker</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="dashboard">


    <!-- ================= SIDEBAR ================= -->

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


        <a href="dashboard.php"
           class="nav-link active">

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


    <!-- ================= MAIN CONTENT ================= -->

    <main class="main-content">


        <!-- HEADER -->

        <div class="top-header">

            <div class="welcome">

                <h2>
                    Welcome back,
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋
                </h2>

                <p>
                    Train harder. Be better.
                </p>

            </div>


            <div class="date-box">

                <?php echo date("D, d M Y"); ?>

            </div>

        </div>


        <!-- ================= HERO ================= -->

        <section class="hero">


            <div class="hero-content">

                <h1>

                    GIVING UP IS NOT

                    <span>
                        IN MY BLOOD.
                    </span>

                </h1>


                <p>
                    Discipline today.<br>
                    Strength tomorrow.
                </p>


                <p class="quote-author">
                    — Nirmal Purja
                </p>


                <a href="../workouts/create.php"
                   class="hero-button">

                    START TODAY'S WORKOUT →

                </a>

            </div>


        </section>


        <!-- ================= ACTION CARDS ================= -->

        <div class="action-grid">


            <a href="../workouts/index.php"
               class="action-card primary">

                <div class="card-icon">
                    ♢
                </div>

                <h3>
                    MY WORKOUTS
                </h3>

                <p>
                    View your saved workouts
                </p>

                <span class="card-arrow">
                    →
                </span>

            </a>


            <a href="../workouts/create.php"
               class="action-card">

                <div class="card-icon">
                    ◇
                </div>

                <h3>
                    CREATE WORKOUT
                </h3>

                <p>
                    Plan your next session
                </p>

                <span class="card-arrow">
                    →
                </span>

            </a>


            <a href="../exercises/index.php"
               class="action-card">

                <div class="card-icon">
                    ▤
                </div>

                <h3>
                    EXERCISE LIBRARY
                </h3>

                <p>
                    Browse all exercises
                </p>

                <span class="card-arrow">
                    →
                </span>

            </a>


            <a href="../exercises/create.php"
               class="action-card">

                <div class="card-icon">
                    ⚙
                </div>

                <h3>
                    ADD EXERCISE
                </h3>

                <p>
                    Add a new exercise to your library
                </p>

                <span class="card-arrow">
                    →
                </span>

            </a>


        </div>


    </main>

</div>

</body>

</html>