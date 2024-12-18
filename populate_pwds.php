<?php
include 'db_connection.php';


$passwords = [
    1 => password_hash('clientpass', PASSWORD_DEFAULT),
    2 => password_hash('sitterpass', PASSWORD_DEFAULT),
    3 => password_hash('handlerpass', PASSWORD_DEFAULT),
    4 => password_hash('anotherclientpass', PASSWORD_DEFAULT),
];

foreach ($passwords as $id => $hash) {
    $sql = "UPDATE Responders SET Password = ? WHERE ResponderID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $hash, $id);

    if ($stmt->execute()) {
        echo "Password updated for ResponderID $id.<br>";
    } else {
        echo "Error updating ResponderID $id: " . $stmt->error . "<br>";
    }

    $stmt->close();
}

$conn->close();
?>
