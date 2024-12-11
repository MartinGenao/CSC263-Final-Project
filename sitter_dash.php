<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in and is a sitter
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Sitter') {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch orders assigned to the logged-in sitter
$sql = "SELECT OrderID, ServiceState, DateCreated, OrderType FROM Orders WHERE ResponderID = ? AND ServiceState = 'Assigned' ORDER BY DateCreated ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];

    // Update the order status to Completed
    $updateSql = "UPDATE Orders SET ServiceState = 'Completed' WHERE OrderID = ? AND ResponderID = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('ii', $orderId, $userId);

    if ($stmt->execute()) {
        $message = "Order $orderId has been marked as completed.";
    } else {
        $message = "Error updating order: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitter Dashboard</title>
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
        .complete-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .complete-form button {
            padding: 5px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .complete-form button:hover {
            background: #005bb5;
        }
        .message {
            margin-bottom: 20px;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
    <header>
        <h1>Sitter Dashboard</h1>
    </header>

    <nav>
        <a href="logout.php">Logout</a>
    </nav>

    <main>
        <h2>Assigned Orders</h2>

        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Service State</th>
                        <th>Date Created</th>
                        <th>Order Type</th>
                        <th>Mark as Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                            <td><?php echo htmlspecialchars($row['ServiceState']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
                            <td><?php echo htmlspecialchars($row['OrderType']); ?></td>
                            <td>
                                <form method="POST" class="complete-form">
                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['OrderID']); ?>">
                                    <button type="submit">Complete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No assigned orders found.</p>
        <?php endif; ?>
    </main>
</body>
</html>
