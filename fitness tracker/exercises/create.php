<?php

session_start();

require_once "../config/database.php";

/* Check login */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$error = "";
$success = "";

$name = "";
$category = "";
$description = "";


/* Save exercise */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? "");
    $category = trim($_POST['category'] ?? "");
    $description = trim($_POST['description'] ?? "");

    /* Validate */
    if (empty($name)) {

        $error = "Please enter exercise name.";

    } elseif (empty($category)) {

        $error = "Please enter exercise category.";

    } else {

        if (!isset($conn) || !($conn instanceof mysqli)) {

            $error = "Database connection failed.";

        } else {

            $check_sql = "SELECT id FROM exercises WHERE name = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);

            if (!$check_stmt) {

                $error = "Failed to prepare query.";

            } else {

                mysqli_stmt_bind_param($check_stmt, "s", $name);
                mysqli_stmt_execute($check_stmt);
                $existing_result = mysqli_stmt_get_result($check_stmt);

                if (mysqli_num_rows($existing_result) > 0) {

                    $error = "That exercise already exists in the library.";

                } else {

                    /* Insert exercise */
                    $sql = "INSERT INTO exercises
                            (name, category, description)
                            VALUES (?, ?, ?)";

                    $stmt = mysqli_prepare($conn, $sql);

                    if (!$stmt) {

                        $error = "Failed to prepare query.";

                    } else {

                        mysqli_stmt_bind_param(
                            $stmt,
                            "sss",
                            $name,
                            $category,
                            $description
                        );

                        if (mysqli_stmt_execute($stmt)) {

                            $success = "Exercise added successfully.";

                            $name = "";
                            $category = "";
                            $description = "";

                        } else {

                            $error = "Failed to save exercise.";

                        }

                        mysqli_stmt_close($stmt);
                    }
                }

                mysqli_stmt_close($check_stmt);
            }
        }
    }
}

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Exercise - Exercise Tracker</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        .form-page {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-card {
            background: #15181c;
            border: 1px solid #292d32;
            border-radius: 10px;
            padding: 30px;
        }

        .form-card h1 {
            font-size: 25px;
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: #777d85;
            font-size: 12px;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #d5d8dc;
            font-size: 12px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            background: #0d0f12;
            border: 1px solid #30343a;
            border-radius: 7px;
            color: white;
            font-size: 13px;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #e3262e;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .message {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .error {
            background: #321316;
            border: 1px solid #6b2025;
            color: #ff8a8f;
        }

        .success {
            background: #10251a;
            border: 1px solid #245d38;
            color: #7de09b;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .save-button {
            border: none;
            background: #e3262e;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .save-button:hover {
            background: #c91d25;
        }

        .cancel-button {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 6px;
            background: #25292e;
            color: #ccc;
            text-decoration: none;
            font-size: 12px;
        }

        .cancel-button:hover {
            background: #30343a;
            color: white;
        }

        .page-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .back-link {
            color: #999;
            text-decoration: none;
            font-size: 12px;
        }

        .back-link:hover {
            color: white;
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


        <a href="../user/dashboard.php" class="nav-link">

            <span class="nav-icon">⌂</span>

            Dashboard

        </a>


        <a href="../workouts/index.php" class="nav-link">

            <span class="nav-icon">▣</span>

            My Workouts

        </a>


        <a href="../workouts/create.php" class="nav-link">

            <span class="nav-icon">＋</span>

            Create Workout

        </a>


        <a href="index.php" class="nav-link">

            <span class="nav-icon">▤</span>

            Exercise Library

        </a>


        <a href="create.php" class="nav-link active">

            <span class="nav-icon">＋</span>

            Add Exercise

        </a>


        <a href="../auth/logout.php" class="nav-link logout">

            <span class="nav-icon">↪</span>

            Logout

        </a>

    </aside>


    <!-- MAIN -->

    <main class="main-content">


        <div class="page-top">

            <div>

                <h2>Add Exercise</h2>

                <p style="color:#777d85; font-size:12px; margin-top:6px;">
                    Add a new exercise to your exercise library.
                </p>

            </div>


            <a href="index.php" class="back-link">
                ← Exercise Library
            </a>

        </div>


        <div class="form-page">

            <div class="form-card">

                <h1>Add New Exercise</h1>

                <p class="form-subtitle">
                    Enter the exercise information below.
                </p>


                <?php if ($error != ""): ?>

                    <div class="message error">

                        <?php echo htmlspecialchars($error); ?>

                    </div>

                <?php endif; ?>


                <?php if ($success != ""): ?>

                    <div class="message success">

                        <?php echo htmlspecialchars($success); ?>

                    </div>

                <?php endif; ?>


                <form method="POST">


                    <div class="form-group">

                        <label>
                            Exercise Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            placeholder="Example: Bench Press"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Category
                        </label>

                        <input
                            type="text"
                            name="category"
                            value="<?php echo htmlspecialchars($category); ?>"
                            placeholder="Example: Chest"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Description
                        </label>

                        <textarea
                            name="description"
                            placeholder="Describe the exercise..."
                        ><?php echo htmlspecialchars($description); ?></textarea>

                    </div>


                    <div class="form-buttons">

                        <button
                            type="submit"
                            class="save-button"
                        >
                            SAVE EXERCISE
                        </button>


                        <a
                            href="index.php"
                            class="cancel-button"
                        >
                            CANCEL
                        </a>

                    </div>


                </form>

            </div>

        </div>


    </main>

</div>

</body>

</html>