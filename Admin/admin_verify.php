<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur'); // must match the timezone used when the TAC was created
require __DIR__ . "/../config/db.php";

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = trim($_POST['admin_id'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $tac_number = trim($_POST['tac_number'] ?? '');
    $current_time = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("SELECT * FROM admin_tac WHERE admin_id = ? AND state = ? AND tac_number = ? AND tac_expiry > ?");
    $stmt->bind_param("ssss", $admin_id, $state, $tac_number, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // TAC is single-use: delete it once it has been accepted
        $del = $conn->prepare("DELETE FROM admin_tac WHERE admin_id = ? AND state = ?");
        $del->bind_param("ss", $admin_id, $state);
        $del->execute();

        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['state'] = $state;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $login_error = "Invalid TAC number or TAC has expired.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Verify</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('../images/background6.jpg');
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
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Admin Verify</h1>
        <form action="admin_verify.php" method="post">
            Admin ID: <input type="text" name="admin_id" required><br>
            State: <input type="text" name="state" required><br>
            TAC Number: <input type="text" name="tac_number" required><br>
            <input type="submit" value="Login">
        </form>
        <?php if (!empty($login_error)): ?>
            <p class="error"><?= $login_error ?></p>
        <?php endif; ?>
    </div>
</body>
</html>



