<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

$userRole = $_SESSION['role']; // Retrieve user role
$orderDetails = null;
$comments = [];
$message = "";

// Get the client's IP address
$clientIp = $_SERVER['REMOTE_ADDR'];

// Normalize IPv6 loopback to IPv4 for consistency
if ($clientIp === '::1') {
    $clientIp = '127.0.0.1';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];

    // Fetch order details including client and sitter information
    $orderSql = "SELECT Orders.OrderID, Orders.ServiceState, Orders.DateCreated, Orders.OrderType, 
                    ClientResponders.FirstName AS ClientFirstName, ClientResponders.LastName AS ClientLastName, 
                    ClientResponders.Phone AS ClientPhone, ClientResponders.Email AS ClientEmail,
                    SitterResponders.FirstName AS SitterFirstName, SitterResponders.LastName AS SitterLastName, 
                    SitterResponders.Role AS SitterRole
             FROM Orders
             LEFT JOIN Responders AS ClientResponders ON Orders.OrderID = 
                 (SELECT OrderID FROM Orders WHERE Orders.OrderID = ? LIMIT 1) -- Fetch client info
             LEFT JOIN Responders AS SitterResponders ON Orders.ResponderID = SitterResponders.ResponderID 
             WHERE Orders.OrderID = ?";
    $stmt = $conn->prepare($orderSql);
    $stmt->bind_param('ii', $orderId, $orderId);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $orderDetails = $result->fetch_assoc();

        // Fetch comments for the order
        $commentSql = "SELECT Comments.CommentText, Comments.Timestamp, Responders.FirstName, Responders.LastName 
                       FROM Comments 
                       LEFT JOIN Responders ON Comments.ResponderID = Responders.ResponderID 
                       WHERE Comments.OrderID = ? 
                       ORDER BY Comments.Timestamp DESC";
        $stmt = $conn->prepare($commentSql);
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $commentsResult = $stmt->get_result();

        while ($row = $commentsResult->fetch_assoc()) {
            $comments[] = $row;
        }
    } else {
        $message = "Order not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query Order</title>
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
        .message {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Query Order</h1>
    </header>

    <nav>
        <?php if ($userRole === 'Sitter'): ?>
            <a href="sitter_dash.php">Back to Sitter Dashboard</a>
        <?php elseif ($userRole === 'Handler'): ?>
            <a href="handler_dash.php">Back to Handler Dashboard</a>
        <?php elseif ($userRole === 'Client'): ?>
            <a href="client_dash.php">Back to Client Dashboard</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </nav>

    <main>
        <h2>Search for an Order</h2>

        <form method="POST" action="">
            <label for="order_id">Order ID:</label>
            <input type="number" id="order_id" name="order_id" required>
            <button type="submit">Search</button>
        </form>

        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($orderDetails): ?>
            <h3>Order Details</h3>
            <table>
                <tr>
                    <th>Order ID</th>
                    <td><?php echo htmlspecialchars($orderDetails['OrderID']); ?></td>
                </tr>
                <tr>
                    <th>Service State</th>
                    <td><?php echo htmlspecialchars($orderDetails['ServiceState']); ?></td>
                </tr>
                <tr>
                    <th>Date Created</th>
                    <td><?php echo htmlspecialchars($orderDetails['DateCreated']); ?></td>
                </tr>
                <tr>
                    <th>Order Type</th>
                    <td><?php echo htmlspecialchars($orderDetails['OrderType']); ?></td>
                </tr>
                <tr>
                    <th>Client Name</th>
                    <td><?php echo htmlspecialchars($orderDetails['ClientFirstName'] . ' ' . $orderDetails['ClientLastName']); ?></td>
                </tr>
                <tr>
                    <th>Client Phone</th>
                    <td><?php echo htmlspecialchars($orderDetails['ClientPhone']); ?></td>
                </tr>
                <tr>
                    <th>Client Email</th>
                    <td><?php echo htmlspecialchars($orderDetails['ClientEmail']); ?></td>
                </tr>
                <tr>
                    <th>Assigned Sitter</th>
                    <td>
                        <?php 
                        echo htmlspecialchars($orderDetails['SitterFirstName'] . ' ' . $orderDetails['SitterLastName']) . ' (' . htmlspecialchars($orderDetails['SitterRole']) . ')';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Client IP Address</th>
                    <td><?php echo htmlspecialchars($clientIp); ?></td>
                </tr>
            </table>

            <h3>Comments</h3>
            <?php if (!empty($comments)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Comment</th>
                            <th>Responder</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($comment['Timestamp']); ?></td>
                                <td><?php echo htmlspecialchars($comment['CommentText']); ?></td>
                                <td><?php echo htmlspecialchars($comment['FirstName'] . ' ' . $comment['LastName']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No comments found for this order.</p>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>


