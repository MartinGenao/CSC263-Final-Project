<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Client') {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$message = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order_id'])) {
    $orderId = $_POST['confirm_order_id'];
    $clientComment = trim($_POST['client_comment']);

    if (!empty($clientComment)) {
        
        $commentSql = "INSERT INTO Comments (OrderID, ResponderID, CommentText) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($commentSql);
        $stmt->bind_param('iis', $orderId, $userId, $clientComment);

        if ($stmt->execute()) {
            
            header('Location: client_dash.php');
            exit();
        } else {
            $message = "Error adding confirmation comment: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Comment cannot be empty.";
    }
}


$pendingSql = "SELECT Orders.OrderID, Orders.ServiceState, Orders.DateCreated, Orders.OrderType,
               GROUP_CONCAT(CONCAT(Comments.Timestamp, ' ', Responders.FirstName, ' ', Responders.LastName, ': ', Comments.CommentText)
                            ORDER BY Comments.Timestamp DESC SEPARATOR '<br>') AS Comments
               FROM Orders
               LEFT JOIN Comments ON Orders.OrderID = Comments.OrderID
               LEFT JOIN Responders ON Comments.ResponderID = Responders.ResponderID
               WHERE Orders.ResponderID = ? AND Orders.ServiceState = 'Pending'
               GROUP BY Orders.OrderID
               ORDER BY Orders.DateCreated DESC";
$pendingStmt = $conn->prepare($pendingSql);
$pendingStmt->bind_param('i', $userId);
$pendingStmt->execute();
$pendingRequests = $pendingStmt->get_result();


$completedSql = "SELECT Orders.OrderID, Orders.ServiceState, Orders.DateCreated, Orders.OrderType,
                 Responders.FirstName AS SitterFirstName, Responders.LastName AS SitterLastName,
                 GROUP_CONCAT(CONCAT(Comments.Timestamp, ' ', Responders2.FirstName, ' ', Responders2.LastName, ': ', Comments.CommentText)
                              ORDER BY Comments.Timestamp DESC SEPARATOR '<br>') AS Comments
                 FROM Orders
                 LEFT JOIN Responders ON Orders.ResponderID = Responders.ResponderID 
                 LEFT JOIN Comments ON Orders.OrderID = Comments.OrderID
                 LEFT JOIN Responders AS Responders2 ON Comments.ResponderID = Responders2.ResponderID 
                 WHERE Orders.ServiceState = 'Completed'
                 GROUP BY Orders.OrderID
                 ORDER BY Orders.DateCreated DESC";
$completedStmt = $conn->prepare($completedSql);
$completedStmt->execute();
$completedRequests = $completedStmt->get_result();
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
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .message {
            color: green;
            font-weight: bold;
        }
        textarea {
            width: 100%;
            resize: vertical;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .confirm-button {
            padding: 5px 10px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .confirm-button:hover {
            background: #005bb5;
        }
    </style>
</head>
<body>
    <header>
        <h1>Client Dashboard</h1>
    </header>

    <nav>
        <a href="logout.php">Logout</a>
        <a href="query_order.php">Search Orders</a>
        <a href="create_order.php">Create Order</a>
        <a href="order_archive.php">Order Archive</a>
    </nav>

    <main>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <h2>Your Pending Requests</h2>
        <?php if ($pendingRequests->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Service State</th>
                        <th>Date Created</th>
                        <th>Order Type</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pendingRequests->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                            <td><?php echo htmlspecialchars($row['ServiceState']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
                            <td><?php echo htmlspecialchars($row['OrderType']); ?></td>
                            <td><?php echo $row['Comments'] ? nl2br($row['Comments']) : 'No comments yet'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending requests found.</p>
        <?php endif; ?>

        <h2>All Completed Requests</h2>
        <?php if ($completedRequests->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Service State</th>
                        <th>Date Created</th>
                        <th>Order Type</th>
                        <th>Sitter</th>
                        <th>Comments</th>
                        <th>Confirm</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $completedRequests->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                            <td><?php echo htmlspecialchars($row['ServiceState']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
                            <td><?php echo htmlspecialchars($row['OrderType']); ?></td>
                            <td><?php echo htmlspecialchars($row['SitterFirstName'] . ' ' . $row['SitterLastName']); ?></td>
                            <td><?php echo $row['Comments'] ? nl2br($row['Comments']) : 'No comments yet'; ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="confirm_order_id" value="<?php echo htmlspecialchars($row['OrderID']); ?>">
                                    <textarea name="client_comment" placeholder="Add a comment" required></textarea>
                                    <button type="submit" class="confirm-button">Confirm</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No completed requests found.</p>
        <?php endif; ?>
    </main>
</body>
</html>
