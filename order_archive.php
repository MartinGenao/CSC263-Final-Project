<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch completed orders
$sql = "SELECT o.OrderID, o.DateCreated, o.OrderType, r.FirstName, r.LastName, r.Role 
        FROM Orders o 
        LEFT JOIN Responders r ON o.ResponderID = r.ResponderID 
        WHERE o.ServiceState = 'Completed' 
        ORDER BY o.DateCreated DESC";
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
    </style>
</head>
<body>
    <header>
        <h1>Order Archive</h1>
    </header>

    <nav>
        <a href="logout.php">Logout</a>
    </nav>

    <main>
        <h2>Completed Orders</h2>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date Created</th>
                        <th>Order Type</th>
                        <th>Assigned Responder</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
                            <td><?php echo htmlspecialchars($row['OrderType']); ?></td>
                            <td>
                                <?php
                                echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) . ' (' . htmlspecialchars($row['Role']) . ')';
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No completed orders found.</p>
        <?php endif; ?>
    </main>
</body>
</html>
