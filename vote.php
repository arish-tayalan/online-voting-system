<?php
session_start();

// Set the default time zone
date_default_timezone_set('Asia/Kuala_Lumpur'); 

// Database connection setup
require __DIR__ . "/config/db.php";

// Check if logout has been requested
if (isset($_POST['logout'])) {
    if (isset($_SESSION['user_id'])) {
        // Clear the "already logged in" flag so the account can sign in again
        $out = $conn->prepare("UPDATE users SET is_logged_in = FALSE WHERE id = ?");
        $out->bind_param("i", $_SESSION['user_id']);
        $out->execute();
    }
    session_destroy();
    header("Location: signin.php");
    exit;
}

// Only signed-in, identity-verified voters can vote
if (!isset($_SESSION['user_id']) || empty($_SESSION['verified'])) {
    header("Location: signin.php");
    exit;
}

$error = null; // Variable to store error message
$candidate = null; // Variable to store candidate details

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['candidate'])) {
    $candidate_id = (int)$_POST['candidate'];
    $user_id = $_SESSION['user_id']; // Get user ID from session
    $vote_time = date("Y-m-d H:i:s"); // Capture the server time

    // Fetch the user's IC number and state
    $stmt = $conn->prepare("SELECT ic_number, state FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Make sure the candidate actually exists
    $chk = $conn->prepare("SELECT id FROM candidates WHERE id = ?");
    $chk->bind_param("i", $candidate_id);
    $chk->execute();
    $valid_candidate = $chk->get_result()->num_rows > 0;
    $chk->close();

    if (!$valid_candidate) {
        $error = "Please select a valid candidate.";
    } elseif ($user && !empty($user['ic_number'])) {
        $ic_number = $user['ic_number'];
        $state = $user['state'];

        // Check if the user has already voted
        $stmt = $conn->prepare("SELECT * FROM votes WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            // User has already voted
            $error = "You have already voted!";
        } else {
            // Prepare SQL statement to prevent SQL injection and record vote
            $stmt = $conn->prepare("INSERT INTO votes (candidate_id, user_id, ic_number, state, vote_time) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $candidate_id, $user_id, $ic_number, $state, $vote_time);

            // Execute the prepared statement
            if ($stmt->execute()) {
                // Fetch candidate name for user feedback
                $stmt = $conn->prepare("SELECT name FROM candidates WHERE id = ?");
                $stmt->bind_param("i", $candidate_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($candidate_info = $result->fetch_assoc()) {
                    $candidate = $candidate_info['name'];
                }
            } else {
                $error = "Your vote could not be recorded. Please try again.";
            }
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
    <title>Vote Confirmation</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
            background-image: url('images/background5.jpg');
            background-size: cover;
            background-position: center;
        }
        .ticket {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            text-align: center;
            width: 300px;
        }
        .ticket h1 {
            color: #4a90e2;
        }
        .info {
            background-color: #f8f8f8;
            padding: 10px;
            margin-top: 20px;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #367abf;
        }
    </style>
</head>
<body>
<?php if (isset($candidate)): ?>
    <div class="ticket">
        <h1>Thank You for Voting!</h1>
        <div class="info">
            <p>You voted for: <strong><?= htmlspecialchars($candidate) ?></strong></p>
            <p>Voting Time: <strong><?= htmlspecialchars($vote_time) ?></strong></p>
        </div>
        <form method="post">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>
<?php elseif(isset($error)): ?>
    <div class="ticket">
        <h1>Notification</h1>
        <div class="info"><?= htmlspecialchars($error) ?></div>
        <form method="post">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>
<?php endif; ?>
</body>
</html>



