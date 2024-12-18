<?php

include 'db_connection.php';
session_start();

$message = "";

$orderTypes = [];
$orderTypeSql = "SELECT DISTINCT OrderType FROM Orders ORDER BY OrderType ASC";
$orderTypeResult = $conn->query($orderTypeSql);
if ($orderTypeResult->num_rows > 0) {
    while ($row = $orderTypeResult->fetch_assoc()) {
        $orderTypes[] = $row['OrderType'];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderType = trim($_POST['orderType']);
    $commentText = trim($_POST['comment']);
    $clientIp = $_SERVER['REMOTE_ADDR']; s

    
    if ($clientIp === '::1') {
        $clientIp = '127.0.0.1';
    }

    $dateCreated = date('Y-m-d H:i:s'); 
    $serviceState = 'pending'; 

    
    $responderID = $_SESSION['user_id'];

    
    if (empty($orderType)) {
        $message = "Order type is required.";
    } elseif (empty($commentText)) {
        $message = "A comment is required to create an order.";
    } else {
        
        $sql = "INSERT INTO Orders (OrderType, DateCreated, ServiceState, ResponderID) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssi', $orderType, $dateCreated, $serviceState, $responderID);

        if ($stmt->execute()) {
            $orderID = $stmt->insert_id; 

          
            $commentSql = "INSERT INTO Comments (OrderID, ResponderID, CommentText) VALUES (?, ?, ?)";
            $commentStmt = $conn->prepare($commentSql);
            $commentStmt->bind_param('iis', $orderID, $responderID, $commentText);

            if ($commentStmt->execute()) {
                $message = "Order created successfully!";
            } else {
                $message = "Error adding comment: " . $commentStmt->error;
            }

            $commentStmt->close();
        } else {
            $message = "Error creating order: " . $stmt->error;
        }

        $stmt->close();

        
        $_SESSION['client_ip'] = $clientIp;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order</title>
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
        main {
            padding: 20px;
            text-align: center;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form select, form textarea, form button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form textarea {
            height: 100px;
            resize: vertical;
        }
        form button {
            background: #0073e6;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background: #005bb5;
        }
        .message {
            margin-top: 20px;
            font-weight: bold;
        }
        .back-button {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background: #0073e6;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background: #005bb5;
        }
    </style>
</head>
<body>
    <header>
        <h1>Create a New Order</h1>
    </header>

    <main>
        <form method="POST" action="">
            <label for="orderType">Order Type:</label>
            <select id="orderType" name="orderType" required>
                <option value="" disabled selected>Select an order type</option>
                <?php foreach ($orderTypes as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>">
                        <?php echo htmlspecialchars($type); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="comment">Add a Comment:</label>
            <textarea id="comment" name="comment" required placeholder="Enter any special instructions or details here..."></textarea>

            <button type="submit">Create Order</button>
        </form>

        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <a href="client_dash.php" class="back-button">Back to Client Dashboard</a>
    </main>
</body>
</html>

