<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($conn)) {
    die("Database connection is not available.");
}

if (isset($_SESSION['user_id'])) {
    header("Location: ../user/dashboard.php");
    exit();
}

$email = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Please enter your email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: ../user/dashboard.php");
            exit();
        } else {
            $error = "Wrong email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Exercise Tracker</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: #0d0d0f; color: #eee; font-family: Segoe UI, Arial, sans-serif; padding: 16px;
        }
        .box { width: 100%; max-width: 360px; }
        h1 { margin: 0 0 24px; font-size: 22px; letter-spacing: 1px; }
        h1 span { color: #e3262e; }
        label { display: block; margin: 14px 0 5px; font-size: 14px; color: #aaa; }
        input {
            width: 100%; padding: 11px; background: #17171a; color: #fff;
            border: 1px solid #2c2c31; border-radius: 6px; font-size: 15px;
        }
        input:focus { outline: none; border-color: #e3262e; }
        button {
            margin-top: 22px; width: 100%; padding: 12px; border: none; border-radius: 6px;
            background: #e3262e; color: #fff; font-size: 15px; cursor: pointer;
        }
        button:hover { background: #c71f27; }
        .error { margin-bottom: 4px; padding: 10px; background: #2a1213; border-left: 3px solid #e3262e; font-size: 14px; }
        p { margin-top: 20px; font-size: 14px; color: #aaa; }
        a { color: #e3262e; }
    </style>
</head>
<body>
<div class="box">
    <h1>EXERCISE <span>TRACKER</span></h1>

    <?php if ($error !== ""): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Log in</button>
    </form>

    <p>No account yet? <a href="register.php">Register</a></p>
</div>
</body>
</html>