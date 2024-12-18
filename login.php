<?php
session_start();
include 'db_connection.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT ResponderID, Role, Password FROM Responders WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        
        if (password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['ResponderID'];
            $_SESSION['role'] = $user['Role'];

            
            switch ($user['Role']) {
                case 'Client':
                    header('Location: client_dash.php');
                    break;
                case 'Handler':
                    header('Location: handler_dash.php');
                    break;
                case 'Sitter':
                    header('Location: sitter_dash.php');
                    break;
                default:
                    $message = "Invalid role detected.";
            }
            exit();
        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "Invalid email or password.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSIRT Login</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 80px);
            background-color: #f4f4f4;
        }
        .login-container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .login-container h1 {
            margin-bottom: 20px;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-container button:hover {
            background: #005bb5;
        }
        .message {
            color: red;
            margin-top: 15px;
        }
        footer {
            text-align: center;
            padding: 10px;
            background: #333;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <h1>Pet Sitter Response Tracking System</h1>
    </header>

    <main>
        <div class="login-container">
            <h1>Login</h1>
            <form method="POST" action="">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <?php if ($message): ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Team Titans - CSC 263 Final Project</p>
    </footer>
</body>
</html>
