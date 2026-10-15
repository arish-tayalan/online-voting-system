<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur'); // Time zone
require __DIR__ . "/../config/db.php";

$admin_id = '';
$state = '';
$tac_number = '';
$error_message = '';
$current_time = '';
$tac_expiry = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = trim($_POST['admin_id'] ?? '');
    $state = trim($_POST['state'] ?? '');

    // Validate admin ID and state
    $stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ? AND state = ?");
    $stmt->bind_param("ss", $admin_id, $state);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Admin ID and state match, generate TAC
        $tac_number = sprintf("%06d", random_int(0, 999999)); // cryptographically secure
        $tac_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $current_time = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO admin_tac (admin_id, state, tac_number, tac_expiry) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE tac_number = ?, tac_expiry = ?");
        $stmt->bind_param("ssssss", $admin_id, $state, $tac_number, $tac_expiry, $tac_number, $tac_expiry);
        if ($stmt->execute()) {
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['state'] = $state;
        } else {
            $error_message = "Could not generate a TAC. Please try again.";
        }
        $stmt->close();
    } else {
        $error_message = "Invalid admin ID or state.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images\background5.jpg');
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 20px;
        }
        .tac-message {
            text-align: center;
            margin-top: 20px;
        }
        .time-message {
            text-align: center;
            margin-top: 20px;
            background-color: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Admin Login</h1>
        <form action="admin_login.php" method="post">
            Admin ID: <input type="text" name="admin_id" required><br>
            State: <input type="text" name="state" required><br>
            <input type="submit" value="Generate TAC">
        </form>
        <?php if (!empty($tac_number)): ?>
            <div class="tac-message">
                <p>Your TAC number is: <?= $tac_number ?></p>
                <p>This TAC is valid for 5 minutes.</p>
                <p><a href="admin_verify.php">Click here to verify</a></p>
            </div>
        <?php endif; ?>
        <?php if (!empty($current_time) && !empty($tac_expiry)): ?>
            <div class="time-message">
                <p>Current Time: <?= $current_time ?></p>
                <p>TAC Expiry Time: <?= $tac_expiry ?></p>
            </div>
        <?php elseif (!empty($error_message)): ?>
            <p class="error"><?= $error_message ?></p>
        <?php endif; ?>
    </div>
</body>
</html>








