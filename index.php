<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php"); // Redirect to login page if not logged in
    exit;
}
if (empty($_SESSION['verified'])) {
    header("Location: verify_identity.php"); // Identity must be verified before voting
    exit;
}

// Database connection setup
require __DIR__ . "/config/db.php";

// Fetch candidates and their images from the database
$sql = "SELECT id, name, image FROM candidates";  // Adjusted to include the image column
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-image: url('images/background1.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 350px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Center align items horizontally */
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            width: 100%;
        }

        label, select, button {
            margin-bottom: 10px;
        }

        button {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            border: none;
            padding: 10px;
            width: 100%; /* Full width button */
        }

        button:hover {
            background-color: #0056b3;
        }

        .candidate {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .candidate img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .candidate label {
            color: black; /* Explicitly setting text color to black */
        }

        .datetime {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 10px;
            border-radius: 10px;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="datetime" id="clock"></div>
    <script>
        function updateClock() {
            const now = new Date();
            const dayName = now.toLocaleDateString('en-us', { weekday: 'long' });
            const date = now.toLocaleDateString('en-us', { year: 'numeric', month: 'long', day: 'numeric' });
            const time = now.toLocaleTimeString('en-us', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });

            document.getElementById('clock').innerHTML = `${dayName}, ${date} ${time}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

    <div class="form-container">
        <h1>Vote for Your Candidate!</h1>
        <form action="vote.php" method="post">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="candidate">';
                    echo '<img src="cand_icon/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                    echo '<label for="candidate_' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</label>';
                    echo '<input type="radio" name="candidate" id="candidate_' . $row['id'] . '" value="' . $row['id'] . '">';
                    echo '</div>';
                }
            } else {
                echo '<p>No candidates available.</p>';
            }
            $conn->close();
            ?>
            <button type="submit">Vote</button>
        </form>
    </div>
</body>
</html>

