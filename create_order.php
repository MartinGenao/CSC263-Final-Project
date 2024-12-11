<?php
// Include database connection
include 'db_connection.php';

// Initialize variables for form submission feedback
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $orderType = $_POST['orderType'];
    $responderID = $_POST['responderID'];
    $dateCreated = date('Y-m-d H:i:s'); // Automatically generate current date and time
    $serviceState = 'pending'; // Default state

    // Validate Responder ID to prevent invalid entries
    if ($responderID <= 0) {
        $message = "Responder ID must be a positive number.";
    } else {
        // Check if ResponderID exists in the database
        $checkSql = "SELECT COUNT(*) AS count FROM Responders WHERE ResponderID = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('i', $responderID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $row = $checkResult->fetch_assoc();

        if ($row['count'] == 0) {
            $message = "Responder ID does not exist.";
        } else {
            // SQL to insert new order
            $sql = "INSERT INTO Orders (OrderType, DateCreated, ServiceState, ResponderID) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssi', $orderType, $dateCreated, $serviceState, $responderID);

            // Execute statement and check for success
            if ($stmt->execute()) {
                $message = "Order created successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        }
        $checkStmt->close();
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
        form input, form select, form button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
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
            <input type="text" id="orderType" name="orderType" required>

            <label for="responderID">Responder ID:</label>
            <input type="number" id="responderID" name="responderID" min="1" required>

            <button type="submit">Create Order</button>
        </form>

        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <a href="psirt_homepage.php" class="back-button">Back to Home</a>
    </main>
</body>
</html>
