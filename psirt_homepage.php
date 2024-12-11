<?php
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSIRT - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        header {
            background: #0073e6;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            position: absolute;
            top: 0;
            width: 100%;
        }
        main {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
        }
        .role-container {
            text-align: center;
            background: linear-gradient(135deg, #0073e6, #005bb5);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            color: white;
            max-width: 400px;
            width: 90%;
        }
        .role-container h1 {
            margin-bottom: 20px;
            color: #fff;
            font-size: 24px;
        }
        .role-container p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #e0e0e0;
        }
        .role-container a {
            display: inline-block;
            margin: 10px;
            padding: 15px 30px;
            background: #fff;
            color: #0073e6;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .role-container a:hover {
            background: #f4f4f4;
            color: #005bb5;
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
        <h1>Welcome to the Pet Sitter Response Tracking System (PSIRT)</h1>
    </header>

    <main>
        <div class="role-container">
            <h1>Welcome to PSIRT</h1>
            <p>Select your role to continue:</p>
            <a href="login.php?role=handler">Handler</a>
            <a href="login.php?role=client">Client</a>
            <a href="login.php?role=sitter">Sitter</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Team Titans - CSC 263 Final Project</p>
    </footer>
</body>
</html>