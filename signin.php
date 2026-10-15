<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require __DIR__ . "/config/db.php";

    $username = trim($_POST['username'] ?? '');
    $stmt = $conn->prepare("SELECT id, password, is_logged_in FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if ($user['is_logged_in']) {
            $login_error = "This account is already logged in.";
        } elseif (password_verify($_POST['password'], $user['password'])) {
            // Update the database to mark the user as logged in
            $update_stmt = $conn->prepare("UPDATE users SET is_logged_in = TRUE WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();

            session_regenerate_id(true); // prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            header('Location: verify_identity.php');
            exit;
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Invalid username or password.";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
    <style>
         body {  
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
            background-image: url('images/background2.jpg');
            background-size: cover;
            background-position: center;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2em;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            width: 300px;
            align-items: center;
        }
        h1 {
            text-align: center;
            color: #5c67f2;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="password"], input[type="submit"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }
        input[type="submit"] {
            color: white;
            background-color: #5c67f2;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #4a54e1;
        }
        .error {
            color: #ff0000;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Sign In</h1>
        <form action="signin.php" method="post">
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            <input type="submit" value="Login">
            <?php if (!empty($login_error)): ?>
                <p class="error"><?= $login_error ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
