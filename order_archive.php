<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

$userRole = $_SESSION['role']; 


$sql = "SELECT OrderID, ServiceState, DateCreated, OrderType, ResponderID FROM Orders WHERE ServiceState = 'Completed' ORDER BY DateCreated DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Archive</title>
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
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        footer {
            text-align: center;
            padding: 10px;
            background: #333;
            color: white;
            position: absolute;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <h1>Order Archive</h1>
    </header>

    <nav>
        <?php if ($userRole === 'Sitter'): ?>
            <a href="sitter_dash.php">Back to Sitter Dashboard</a>
        <?php elseif ($userRole === 'Handler'): ?>
            <a href="handler_dash.php">Back to Handler Dashboard</a>
        <?php else: ?>
            <a href="client_dash.php">Back to Client Dashboard</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </nav>

    <main>
        <h2>Completed Orders</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Service State</th>
                        <th>Date Created</th>
                        <th>Order Type</th>
                        <th>Responder ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                            <td><?php echo htmlspecialchars($row['ServiceState']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
                            <td><?php echo htmlspecialchars($row['OrderType']); ?></td>
                            <td><?php echo htmlspecialchars($row['ResponderID']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No archived orders found.</p>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; 2024 Team Titans - CSC 263 Final Project</p>
    </footer>
</body>
</html>
