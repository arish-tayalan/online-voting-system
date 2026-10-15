<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_verify.php");
    exit;
}

require __DIR__ . "/../config/db.php";

$state = $_SESSION['state'];

$sql = "SELECT v.id, u.username, v.vote_time, c.name AS candidate_name FROM votes v 
        JOIN users u ON v.user_id = u.id 
        JOIN candidates c ON v.candidate_id = c.id 
        WHERE u.state = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $state);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('../images/background7.jpg');
            background-size: cover;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .logout {
            display: block;
            width: 100px;
            margin: 20px auto;
            padding: 10px;
            text-align: center;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .logout:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <h2>Vote Details for State: <?= htmlspecialchars($state) ?></h2>
        <table>
            <tr>
                <th>Vote ID</th>
                <th>Username</th>
                <th>Vote Time</th>
                <th>Candidate</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['vote_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['candidate_name']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No votes recorded.</td></tr>";
            }
            ?>
        </table>
        <a href="admin_logout.php" class="logout">Logout</a>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>


