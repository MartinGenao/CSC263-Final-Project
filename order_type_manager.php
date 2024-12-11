<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = "";

// Handle adding a new order type
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_order_type'])) {
    $newOrderType = $_POST['new_order_type'];

    // Check if the order type already exists
    $checkSql = "SELECT COUNT(*) AS count FROM Orders WHERE OrderType = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param('s', $newOrderType);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $message = "Order type already exists.";
    } else {
        // Insert new order type
        $insertSql = "INSERT INTO Orders (OrderType) VALUES (?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param('s', $newOrderType);

        if ($stmt->execute()) {
            $message = "New order type added successfully.";
        } else {
            $message = "Error adding order type: " . $stmt->error;
        }
    }
    $stmt->close();
}

// Fetch all distinct order types
$sql = "SELECT DISTINCT OrderType FROM Orders ORDER BY OrderType ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Type Management</title>
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
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .form-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Order Type Management</h1>
    </header>

    <nav>
        <a href="logout.php">Logout</a>
    </nav>

    <main>
        <h2>Manage Order Types</h2>

        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <label for="new_order_type">Add New Order Type:</label>
                <input type="text" id="new_order_type" name="new_order_type" required>
                <button type="submit">Add</button>
            </form>
        </div>

        <h3>Existing Order Types</h3>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['OrderType']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No order types found.</p>
        <?php endif; ?>
    </main>
</body>
</html>