<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Handler') {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id']; 

$sql = "SELECT Orders.OrderID, Orders.ServiceState, Orders.DateCreated, Orders.OrderType,
        GROUP_CONCAT(CONCAT(Comments.Timestamp, ' ', Responders.FirstName, ' ', Responders.LastName, ': ', Comments.CommentText) 
                     ORDER BY Comments.Timestamp ASC SEPARATOR '<br>') AS Comments
        FROM Orders
        LEFT JOIN Comments ON Orders.OrderID = Comments.OrderID
        LEFT JOIN Responders ON Comments.ResponderID = Responders.ResponderID
        WHERE Orders.ServiceState = 'Pending'
        GROUP BY Orders.OrderID
        ORDER BY Orders.DateCreated ASC";
$result = $conn->query($sql);


$sittersSql = "SELECT ResponderID, CONCAT(FirstName, ' ', LastName) AS SitterName FROM Responders WHERE Role = 'Sitter'";
$sittersResult = $conn->query($sittersSql);

/
$sittersOptions = [];
if ($sittersResult->num_rows > 0) {
    while ($sitter = $sittersResult->fetch_assoc()) {
        $sittersOptions[] = $sitter;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['order_id'], $_POST['comment'])) {
        
        $orderId = $_POST['order_id'];
        $commentText = trim($_POST['comment']);

        if (!empty($commentText)) {
            $commentSql = "INSERT INTO Comments (OrderID, ResponderID, CommentText) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($commentSql);
            $stmt->bind_param('iis', $orderId, $userId, $commentText);
            if ($stmt->execute()) {
                $message = "Comment added successfully.";
            } else {
                $message = "Error adding comment: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Comment cannot be empty.";
        }
    } elseif (isset($_POST['order_id'], $_POST['responder_id'])) {
        
        $orderId = $_POST['order_id'];
        $responderId = $_POST['responder_id'];

        $updateSql = "UPDATE Orders SET ServiceState = 'Assigned', ResponderID = ? WHERE OrderID = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param('ii', $responderId, $orderId);

        if ($stmt->execute()) {
            $message = "Order $orderId has been assigned successfully.";
        } else {
            $message = "Error assigning order: " . $stmt->error;
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
    <title>Handler Dashboard</title>
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
        .assign-form, .comment-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 5px;
        }
        .assign-form button, .comment-form button {
            padding: 5px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .assign-form button:hover, .comment-form button:hover {
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
    </style>
</head>
<body>
    <header>
        <h1>Handler Dashboard</h1>
    </header>

    <nav>
        <a href="logout.php">Logout</a>
        <a href="query_order.php">Search Orders</a>
        <a href="order_archive.php">Order Archive</a>
        <a href="order_type_manager.php">Manage Order Types</a>
    </nav>

    <main>
        <h2>Pending Service Requests</h2>

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
                            <td>
                                <?php echo $row['Comments'] ? nl2br($row['Comments']) : 'No comments yet'; ?>
                            </td>
                            <td>
                                <form method="POST" class="assign-form">
                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['OrderID']); ?>">
                                    <select name="responder_id" required>
                                        <option value="" disabled selected>Select a sitter</option>
                                        <?php foreach ($sittersOptions as $sitter): ?>
                                            <option value="<?php echo htmlspecialchars($sitter['ResponderID']); ?>">
                                                <?php echo htmlspecialchars($sitter['SitterName']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit">Assign</button>
                                </form>
                                <form method="POST" class="comment-form">
                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['OrderID']); ?>">
                                    <textarea name="comment" placeholder="Add a comment"></textarea>
                                    <button type="submit">Add Comment</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending service requests found.</p>
        <?php endif; ?>
    </main>
</body>
</html>


