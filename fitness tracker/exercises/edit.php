<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Database connection failed.");
}

$error = "";
$success = "";

$exercise_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($exercise_id <= 0 && isset($_POST['exercise_id'])) {
    $exercise_id = (int) $_POST['exercise_id'];
}

if ($exercise_id <= 0) {
    header("Location: index.php");
    exit();
}

$name = "";
$category = "";
$description = "";

$stmt = $conn->prepare("SELECT id, name, category, description FROM exercises WHERE id = ?");

if (!$stmt) {
    die("Failed to prepare exercise query: " . $conn->error);
}

$stmt->bind_param("i", $exercise_id);
$stmt->execute();
$result = $stmt->get_result();
$exercise = $result->fetch_assoc();
$stmt->close();

if (!$exercise) {
    header("Location: index.php");
    exit();
}

$name = $exercise['name'];
$category = $exercise['category'];
$description = $exercise['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? "");
    $category = trim($_POST['category'] ?? "");
    $description = trim($_POST['description'] ?? "");

    if ($name === "") {
        $error = "Please enter exercise name.";
    } elseif ($category === "") {
        $error = "Please enter exercise category.";
    } else {
        $stmt = $conn->prepare("UPDATE exercises SET name = ?, category = ?, description = ? WHERE id = ?");

        if (!$stmt) {
            $error = "Failed to prepare update query.";
        } else {
            $stmt->bind_param("sssi", $name, $category, $description, $exercise_id);

            if ($stmt->execute()) {
                $success = "Exercise updated successfully.";
                $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                $category = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
                $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
            } else {
                $error = "Failed to update exercise.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Exercise - Exercise Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <div class="logo">
            <h1>EXERCISE<br><span>TRACKER</span></h1>
            <p>TRAIN • TRACK • IMPROVE</p>
        </div>

        <div class="nav-title">MAIN MENU</div>

        <a href="../user/dashboard.php" class="nav-link">
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>

        <a href="../workouts/index.php" class="nav-link">
            <span class="nav-icon">▣</span>
            My Workouts
        </a>

        <a href="index.php" class="nav-link active">
            <span class="nav-icon">▤</span>
            Exercise Library
        </a>

        <a href="create.php" class="nav-link">
            <span class="nav-icon">＋</span>
            Add Exercise
        </a>

        <a href="../auth/logout.php" class="nav-link logout">
            <span class="nav-icon">↪</span>
            Logout
        </a>

    </aside>

    <main class="main-content">

        <div class="top-header">
            <div class="welcome">
                <h2>Edit Exercise</h2>
                <p>Update the selected exercise in your library.</p>
            </div>
        </div>

        <div class="form-page">
            <div class="form-card">

                <?php if ($error !== ""): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success !== ""): ?>
                    <div class="message success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="exercise_id" value="<?php echo (int) $exercise_id; ?>">

                    <div class="form-group">
                        <label for="name">Exercise Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($category); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="save-button">Save Changes</button>
                        <a href="index.php" class="cancel-button">Cancel</a>
                    </div>
                </form>

            </div>
        </div>

    </main>

</div>

</body>
</html>
