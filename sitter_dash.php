<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Sitter') {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];


$sql = "SELECT Orders.OrderID, Orders.ServiceState, Orders.DateCreated, Orders.OrderType,
               FirstCommenter.FirstName AS ClientFirstName, FirstCommenter.LastName AS ClientLastName,
               FirstCommenter.Phone AS ClientPhone, FirstCommenter.Email AS ClientEmail,
               GROUP_CONCAT(CONCAT(Comments.Timestamp, ' ', Responders2.FirstName, ' ', Responders2.LastName, ': ', Comments.CommentText)
                            ORDER BY Comments.Timestamp ASC SEPARATOR '<br>') AS Comments
        FROM Orders
        LEFT JOIN Responders AS FirstCommenter 
            ON FirstCommenter.ResponderID = (
                SELECT c.ResponderID
                FROM Comments c
                WHERE c.OrderID = Orders.OrderID
                ORDER BY c.Timestamp ASC
                LIMIT 1
            )
        LEFT JOIN Comments ON Orders.OrderID = Comments.OrderID
        LEFT JOIN Responders AS Responders2 ON Comments.ResponderID = Responders2.ResponderID
        WHERE Orders.ServiceState = 'Assigned'
        GROUP BY Orders.OrderID
        ORDER BY Orders.DateCreated ASC";

$stmt = $conn->prepare($sql);
//$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['order_id'], $_POST['comment'])) {
        
        $orderId = $_POST['order_id'];
        $commentText = trim($_POST['comment']);

        if (!empty($commentText)) {
            $checkCommentSql = "SELECT COUNT(*) AS count FROM Comments WHERE OrderID = ? AND ResponderID = ?";
            $checkCommentStmt = $conn->prepare($checkCommentSql);
            $checkCommentStmt->bind_param('ii', $orderId, $userId);
            $checkCommentStmt->execute();
            $checkCommentResult = $checkCommentStmt->get_result();
            $row = $checkCommentResult->fetch_assoc();

            if ($row['count'] > 0) {
                $message = "You have already commented on this order.";
            } else {
                $commentSql = "INSERT INTO Comments (OrderID, ResponderID, CommentText) VALUES (?, ?, ?)";
                $commentStmt = $conn->prepare($commentSql);
                $commentStmt->bind_param('iis', $orderId, $userId, $commentText);
                if ($commentStmt->execute()) {
                    $message = "Comment added successfully.";
                } else {
                    $message = "Error adding comment: " . $commentStmt->error;
                }
                $commentStmt->close();
            }
            $checkCommentStmt->close();
        } else {
            $message = "Comment cannot be empty.";
        }
    } elseif (isset($_POST['order_id'], $_POST['complete'])) {
        
        $orderId = $_POST['order_id'];

        $updateSql = "UPDATE Orders SET ServiceState = 'Completed' WHERE OrderID = ? AND ResponderID = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param('ii', $orderId, $userId);

        if ($stmt->execute()) {
            $message = "Order $orderId has been marked as completed.";
        } else {
            $message = "Error updating order: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['order_id'], $_POST['deny'])) {
        
        $orderId = $_POST['order_id'];

        $updateSql = "UPDATE Orders SET ServiceState = 'Pending' WHERE OrderID = ? AND ResponderID = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param('ii', $orderId, $userId);

        if ($stmt->execute()) {
            $message = "Order $orderId has been set back to Pending.";
        } else {
            $message = "Error updating order: " . $stmt->error;
        }
        $stmt->close();
    }
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
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .action-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 5px;
        }
        .action-form button {
            padding: 5px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .action-form button:hover {
            background: #005bb5;
        }
        .message {
            margin-bottom: 20px;
            font-weight: bold;
            color: green;
        }
        textarea {
            width: 100%;
            resize: vertical;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
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
        <h1>Sitter Dashboard</h1>
    </header>

    <nav>
        <a href="logout.php">Logout</a>
        <a href="query_order.php">Search Orders</a>
        <a href="order_archive.php">Order Archive</a>
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
                        <th>Comments</th>
                        <th>Client Info</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                            <td><?php echo htmlspecialchars($row['ServiceState']); ?></td>
                            <td><?php echo htmlspecialchars($row['DateCreated']); ?></td>
                            <td><?php echo htmlspecialchars($row['OrderType']); ?></td>
                            <td><?php echo $row['Comments'] ? nl2br($row['Comments']) : 'No comments yet'; ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['ClientFirstName'] . ' ' . $row['ClientLastName']); ?><br>
                                Phone: <?php echo htmlspecialchars($row['ClientPhone']); ?><br>
                                Email: <?php echo htmlspecialchars($row['ClientEmail']); ?>
                            </td>
                            <td>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['OrderID']); ?>">
                                    <textarea name="comment" placeholder="Add a comment"></textarea>
                                    <button type="submit">Add Comment</button>
                                </form>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['OrderID']); ?>">
                                    <input type="hidden" name="complete" value="1">
                                    <button type="submit">Mark as Completed</button>
                                </form>
                                <form method="POST" class="action-form">
                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['OrderID']); ?>">
                                    <input type="hidden" name="deny" value="1">
                                    <button type="submit" style="background: #e60000;">Deny Request</button>
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
    <footer>
        <p>&copy; 2024 Team Titans - CSC 263 Final Project</p>
    </footer>
</body>
</html>
