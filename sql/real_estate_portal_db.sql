-- =========================================================
-- Real Estate Agency Portal Starter SQL
-- Spring 2026
-- =========================================================

CREATE DATABASE IF NOT EXISTS real_estate_portal_db;
USE real_estate_portal_db;

-- Drop tables if they exist
DROP TABLE IF EXISTS Favorites;
DROP TABLE IF EXISTS Transactions;
DROP TABLE IF EXISTS Inquiries;
DROP TABLE IF EXISTS Properties;
DROP TABLE IF EXISTS Users;

-- Users Table
CREATE TABLE Users (
    userId INT NOT NULL AUTO_INCREMENT,
    userName VARCHAR(50) NOT NULL UNIQUE,
    contactInfo VARCHAR(200),
    passwordHash VARCHAR(255) NOT NULL,
    userType ENUM('agent', 'buyer', 'renter') NOT NULL,
    PRIMARY KEY (userId)
);

-- Properties Table
CREATE TABLE IF NOT EXISTS Properties (
    propertyId INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    propertyType VARCHAR(50) NOT NULL,
    address VARCHAR(200) NOT NULL,
    city VARCHAR(100) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    status ENUM('available', 'sold', 'rented') NOT NULL DEFAULT 'available',
    agentId INT NOT NULL,
    PRIMARY KEY (propertyId),
    FOREIGN KEY (agentId) REFERENCES Users(userId)
);

-- Inquiries Table
CREATE TABLE IF NOT EXISTS Inquiries (
    inquiryId INT NOT NULL AUTO_INCREMENT,
    userId INT NOT NULL,
    propertyId INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    inquiryDate DATETIME NOT NULL,
    PRIMARY KEY (inquiryId),
    FOREIGN KEY (userId) REFERENCES Users(userId),
    FOREIGN KEY (propertyId) REFERENCES Properties(propertyId)
);

-- Transactions Table
CREATE TABLE IF NOT EXISTS Transactions (
    transactionId INT NOT NULL AUTO_INCREMENT,
    propertyId INT NOT NULL,
    userId INT NOT NULL,
    transactionType ENUM('sale', 'rental') NOT NULL,
    transactionDate DATETIME NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (transactionId),
    FOREIGN KEY (propertyId) REFERENCES Properties(propertyId),
    FOREIGN KEY (userId) REFERENCES Users(userId)
);

-- Favorites Table
CREATE TABLE IF NOT EXISTS Favorites (
    favoriteId INT NOT NULL AUTO_INCREMENT,
    userId INT NOT NULL,
    propertyId INT NOT NULL,
    savedDate DATETIME NOT NULL,
    PRIMARY KEY (favoriteId),
    FOREIGN KEY (userId) REFERENCES Users(userId),
    FOREIGN KEY (propertyId) REFERENCES Properties(propertyId)
);

-- Stored Procedure: AddOrUpdateUser
DELIMITER //
CREATE PROCEDURE AddOrUpdateUser(
    IN in_userId INT,
    IN in_userName VARCHAR(50),
    IN in_contactInfo VARCHAR(200),
    IN in_passwordHash VARCHAR(255),
    IN in_userType ENUM('agent','buyer','renter')
)
BEGIN
    IF in_userId IS NULL OR in_userId = 0 THEN
        INSERT INTO Users (userName, contactInfo, passwordHash, userType)
        VALUES (in_userName, in_contactInfo, in_passwordHash, in_userType);
    ELSE
        UPDATE Users SET userName = in_userName, contactInfo = in_contactInfo, passwordHash = in_passwordHash, userType = in_userType
        WHERE userId = in_userId;
    END IF;
END //
DELIMITER ;

-- Stored Procedure: ProcessTransaction
DELIMITER //
CREATE PROCEDURE ProcessTransaction(
    IN in_propertyId INT,
    IN in_userId INT,
    IN in_transactionType ENUM('sale','rental'),
    IN in_amount DECIMAL(12,2)
)
BEGIN
    INSERT INTO Transactions (propertyId, userId, transactionType, transactionDate, amount)
    VALUES (in_propertyId, in_userId, in_transactionType, NOW(), in_amount);
    IF in_transactionType = 'sale' THEN
        UPDATE Properties SET status = 'sold' WHERE propertyId = in_propertyId;
    ELSEIF in_transactionType = 'rental' THEN
        UPDATE Properties SET status = 'rented' WHERE propertyId = in_propertyId;
    END IF;
END //
DELIMITER ;

-- View: PropertyListingView
CREATE OR REPLACE VIEW PropertyListingView AS
SELECT p.propertyId, p.title, p.propertyType, p.city, p.price, p.status, u.userName AS agentName
FROM Properties p
JOIN Users u ON p.agentId = u.userId;

DELIMITER //

-- Trigger: AfterTransactionInsert
CREATE TRIGGER AfterTransactionInsert
AFTER INSERT ON Transactions
FOR EACH ROW
BEGIN
    IF NEW.transactionType = 'sale' THEN
        UPDATE Properties SET status = 'sold' WHERE propertyId = NEW.propertyId;
    ELSEIF NEW.transactionType = 'rental' THEN
        UPDATE Properties SET status = 'rented' WHERE propertyId = NEW.propertyId;
    END IF;
END //
DELIMITER ;

-- ---------------------------------------------------------
-- Sample Data
-- NOTE: Password hashes below are placeholders.
-- ---------------------------------------------------------
INSERT INTO Users (userName, contactInfo, passwordHash, userType) VALUES
('agent_maria', 'maria@agency.com', '$2y$10$examplehash0000000000000000000000000000000000000', 'agent'),
('buyer_james', 'james@email.com', '$2y$10$examplehash0000000000000000000000000000000000001', 'buyer'),
('renter_lisa', 'lisa@email.com', '$2y$10$examplehash0000000000000000000000000000000000002', 'renter');

INSERT INTO Properties (title, propertyType, address, city, price, status, agentId) VALUES
('Modern Apartment', 'Apartment', '123 Main St', 'Springfield', 250000.00, 'available', 1),
('Cozy House', 'House', '456 Oak Ave', 'Springfield', 350000.00, 'available', 1),
('Downtown Condo', 'Condo', '789 Pine Rd', 'Metropolis', 200000.00, 'available', 1);

INSERT INTO Inquiries (userId, propertyId, message, inquiryDate) VALUES
(2, 1, 'Is this apartment still available?', NOW()),
(3, 2, 'Can I schedule a viewing for the house?', NOW()),
(2, 3, 'What is the HOA fee for the condo?', NOW());

INSERT INTO Transactions (propertyId, userId, transactionType, transactionDate, amount) VALUES
(1, 2, 'sale', NOW(), 250000.00),
(2, 3, 'rental', NOW(), 1800.00),
(3, 2, 'sale', NOW(), 200000.00);

INSERT INTO Favorites (userId, propertyId, savedDate) VALUES
(2, 1, NOW()),
(3, 2, NOW()),
(2, 3, NOW());