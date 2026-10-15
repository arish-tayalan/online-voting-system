<?php
session_start();

// Database connection setup
require __DIR__ . "/config/db.php";

$error_message = ''; // Initialize error message
$success_message = ''; // Initialize success message

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || strlen($password) < 8) {
        $error_message = "Please enter a username and a password of at least 8 characters.";
    } else {

    // Check if username already exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error_message = "Username already registered. Please choose another username.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $insert = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $insert->bind_param("ss", $username, $hashed_password);
        if ($insert->execute()) {
            $success_message = "Registered successfully. <a href='signin.php'>Login here</a>";
        } else {
            $error_message = "Registration failed. Please try again.";
        }
        $insert->close();
    }
    $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <style>
        body {  
            font-family: Arial, sans-serif;
            background-image: url('images/background3.jpg'); /* Path to background image */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 300px;
            position: relative;
        }
        h1 {
            margin-bottom: 20px;
            width: 100%;
            text-align: center;
            color: #5c67f2;
        }
        input[type="text"],
        input[type="password"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }
        input[type="submit"] {
            font-size: 16px;
            font-weight: bold;
            color: white;
            background-color: #5c67f2;
            border: none;
            padding: 10px 20px; /* Adjusted padding for better appearance */
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            display: block;
            margin: 0 auto; /* Center the button */
        }
        input[type="submit"]:hover {
            background-color: #4a54e1;
        }
        .message {
            text-align: center;
            margin-top: 20px;
        }
        .error {
            color: #ff0000;
        }
        .success {
            color: #28a745;
        }
        .signin-link {
            margin-top: 10px;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Sign Up</h1>
        <form action="signup.php" method="post">
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            <input type="submit" value="Register">
            <?php if (!empty($error_message)): ?>
                <p class="message error"><?= $error_message ?></p>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <p class="message success"><?= $success_message ?></p>
            <?php endif; ?>
        </form>
        <div class="signin-link">
            Already have an account? <a href="signin.php">Sign in here</a>
        </div>
    </div>
</body>
</html>




