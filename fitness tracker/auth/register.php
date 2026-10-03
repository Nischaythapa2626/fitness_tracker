<?php

require_once "../config/database.php";

/** @var mysqli $conn */

$message = "";
$message_type = "";
$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } else {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password_hash)
                VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $name,
            $email,
            $password_hash
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Registration successful.";
            $message_type = "success";
            $name = "";
            $email = "";
        } else {
            $message = "Registration failed. Email may already exist.";
            $message_type = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Exercise Tracker</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0d0d0f;
            color: #eee;
            font-family: Segoe UI, Arial, sans-serif;
            padding: 16px;
        }
        .box {
            width: 100%;
            max-width: 420px;
            background: #17171a;
            border: 1px solid #2c2c31;
            border-radius: 10px;
            padding: 26px 22px;
        }
        h1 {
            margin: 0 0 22px;
            font-size: 23px;
            letter-spacing: 1px;
            font-weight: 700;
            color: #fff;
        }
        h1 span { color: #e3262e; }
        .message {
            margin-bottom: 18px;
            padding: 11px 12px;
            border-radius: 6px;
            font-size: 14px;
        }
        .message.error {
            background: rgba(227, 38, 46, 0.12);
            border-left: 3px solid #e3262e;
            color: #f6b0b4;
        }
        .message.success {
            background: rgba(35, 122, 72, 0.12);
            border-left: 3px solid #2e8b57;
            color: #bfe9cf;
        }
        label {
            display: block;
            margin: 14px 0 6px;
            font-size: 13px;
            color: #aaa;
        }
        input {
            width: 100%;
            padding: 11px 12px;
            background: #101114;
            color: #fff;
            border: 1px solid #2c2c31;
            border-radius: 6px;
            font-size: 15px;
        }
        input:focus {
            outline: none;
            border-color: #e3262e;
        }
        button {
            width: 100%;
            margin-top: 22px;
            padding: 12px 14px;
            border: none;
            border-radius: 6px;
            background: #e3262e;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
            font-weight: 600;
        }
        button:hover { background: #c71f27; }
        p {
            margin-top: 18px;
            font-size: 14px;
            color: #aaa;
            text-align: center;
        }
        a { color: #e3262e; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="box">
    <h1>EXERCISE <span>TRACKER</span></h1>

    <?php if ($message !== ""): ?>
        <div class="message <?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Log in</a></p>
</div>
</body>
</html>