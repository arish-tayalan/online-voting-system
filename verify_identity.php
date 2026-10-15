<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit;
}

$error_message = ''; // Initialize error message

// Database connection setup
require __DIR__ . "/config/db.php";

// Already verified? Go straight to the ballot.
$me = $conn->prepare("SELECT ic_number FROM users WHERE id = ?");
$me->bind_param("i", $_SESSION['user_id']);
$me->execute();
$me_row = $me->get_result()->fetch_assoc();
$me->close();
if (!empty($me_row['ic_number'])) {
    $_SESSION['verified'] = true;
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ic_number = trim($_POST['ic_number']); // Trim to remove any accidental whitespace
    $state = trim($_POST['state']);
    $user_id = $_SESSION['user_id']; // Get user ID from session

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM votersdataset WHERE IC = ? AND state = ?");
    $stmt->bind_param("ss", $ic_number, $state);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Check if the IC number is already in the users table
        $check_ic_stmt = $conn->prepare("SELECT id FROM users WHERE ic_number = ? AND id <> ?");
        $check_ic_stmt->bind_param("si", $ic_number, $user_id);
        $check_ic_stmt->execute();
        $ic_result = $check_ic_stmt->get_result();
        
        if ($ic_result->num_rows > 0) {
            $error_message = "This IC number is already registered to another account.";
        } else {
            // IC number and state are valid, update users table
            $update_stmt = $conn->prepare("UPDATE users SET ic_number = ?, state = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $ic_number, $state, $user_id);
            if ($update_stmt->execute()) {
                $_SESSION['verified'] = true;
                header("Location: index.php"); // Redirect to voting page
                exit;
            } else {
                $error_message = "Failed to update IC number and state. Please try again.";
            }
            $update_stmt->close();
        }
        $check_ic_stmt->close();
    } else {
        $error_message = "IC Number or State does not match our records.";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Identity</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('images/background4.jpg'); 
            background-size: cover; 
            background-position: center; 
        }
        .form-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2em;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 300px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="submit"] {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }
        .error {
            color: #ff0000;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Verify Your Identity</h1>
        <form action="verify_identity.php" method="post">
            IC Number: <input type="text" name="ic_number" required><br>
            State: <input type="text" name="state" required><br>
            <input type="submit" value="Verify">
            <?php if (!empty($error_message)): ?>
                <p class="error"><?= $error_message ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>



