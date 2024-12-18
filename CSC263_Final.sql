CREATE DATABASE PetSitter;

USE PetSitter;

CREATE TABLE Responders (
    ResponderID INT AUTO_INCREMENT PRIMARY KEY,
    LastName VARCHAR(100) NOT NULL,
    FirstName VARCHAR(100) NOT NULL,
    Role ENUM('Client', 'Sitter', 'Handler') NOT NULL,
    Phone VARCHAR(15),
    Email VARCHAR(100)
    Password VARCHAR(255) NOT NULL
);

SELECT * FROM Responders;

CREATE TABLE Orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    ServiceState ENUM('Pending', 'Assigned', 'Completed') NOT NULL,
    DateCreated DATETIME NOT NULL,
    OrderType VARCHAR(100) NOT NULL,
    ResponderID INT,
    FOREIGN KEY (ResponderID) REFERENCES Responders(ResponderID) ON DELETE SET NULL
);

SELECT * FROM Orders;

CREATE TABLE Comments (
    CommentID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT,
    ResponderID INT,
    CommentText TEXT NOT NULL,
    Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID) ON DELETE CASCADE,
    FOREIGN KEY (ResponderID) REFERENCES Responders(ResponderID) ON DELETE SET NULL
);

SELECT * FROM Comments;

INSERT INTO Responders (ResponderID, FirstName, LastName, Role, Email, Phone) 
VALUES 
(1, 'Daniyal', 'Tariq Butt', 1, 'daniyal@google.com', '561-0001'),
(2, 'Martin', 'Genao', 2, 'martin@google.com', '561-0002'),
(3, 'Ann', 'Vu', 3, 'ann@google.com', '561-0003'),
(4, 'Sumi', 'Surenkhuu', 1, 'sumi@google.com', '561-0004');

INSERT INTO Orders (OrderID, ServiceState, DateCreated, OrderType, ResponderID)
VALUES 
(1, 'pending', '2024-12-08', 'basic care', 1),
(2, 'assigned', '2024-12-07', 'special care', 2),
(3, 'completed', '2024-12-06', 'full service', 3),
(4, 'pending', '2024-12-05', 'basic care', 4);

INSERT INTO Comments (CommentID, Timestamp, CommentText, ResponderID, OrderID)
VALUES 
(1, '2024-10-01 10:00:00', 'Service in progress, awaiting client review.', 1, 1),
(2, '2023-12-15 09:00:00', 'Service in progress, preparing for the visit.', 2, 2),
(3, '2022-04-08 08:00:00', 'Service completed!', 3, 3),
(4, '2021-09-24 07:00:00', 'Service completed!', 4, 4);

/* Clear Test Data in database and reset auto increment */
DELETE FROM Comments; 
DELETE FROM Orders; 

ALTER TABLE Comments AUTO_INCREMENT = 1; 
ALTER TABLE Orders AUTO_INCREMENT = 1; 
