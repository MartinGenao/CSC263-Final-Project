<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch the orders created by the logged-in client
$sql = "SELECT OrderID, ServiceState, DateCreated, OrderType FROM Orders WHERE ResponderID = ? ORDER BY DateCreated DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background: #0073e6;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
        }
        nav {
            background: #333;
            padding: 10px;
            text-align: center;
        }
        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 3px;
        }
        nav a:hover {
            background: #575757;
        }
        main {
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #0073e6;
            color: white;
        }
        .add-request {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #0073e6;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .add-request:hover {
            background: #005bb5;
        }
    </style>
</head>
<body>
    <header>
        <h1>Client Dashboard</h1>
    </header>

    <nav>
        <a href="create_order.php">Create New Service Request</a>
        <a href="logout.php">Logout</a>
    </nav>

    <main>
        <h2>Your Service Requests</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Service State</th>
                        <th>Date Created</th>
                        <th>Order Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                            <td><?php echo htmlspecialchars($row['ServiceState']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
                            <td><?php echo htmlspecialchars($row['OrderType']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No service requests found.</p>
        <?php endif; ?>

        <a href="create_order.php" class="add-request">Create New Service Request</a>
    </main>
</body>
</html>
