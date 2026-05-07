<?php
require_once __DIR__ . '/Database.php';

class RealEstateDatabase {
        // Agent: Delete a property by propertyId (must check agent ownership in page logic)
        public function deleteProperty(int $propertyId): bool {
            $sql = "DELETE FROM Properties WHERE propertyId = :propertyId";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':propertyId' => $propertyId]);
        }

        // Buyer/Renter: Save a property as favorite
        public function addFavorite(int $userId, int $propertyId): bool {
            $sql = "INSERT INTO Favorites (userId, propertyId, savedDate) VALUES (:userId, :propertyId, NOW())";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':userId' => $userId,
                ':propertyId' => $propertyId
            ]);
        }
    private PDO $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }


    // Add a new user to the database.
    public function addUser(string $userName, string $contactInfo, string $passwordHash, string $userType): bool {
        // TODO:
        // 1. Insert a new user into the Users table using a prepared statement.
        // 2. Return true if successful, false otherwise.
        $sql = "INSERT INTO Users (userName, contactInfo, passwordHash, userType)
                VALUES (:userName, :contactInfo, :passwordHash, :userType)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':userName' => $userName,
            ':contactInfo' => $contactInfo,
            ':passwordHash' => $passwordHash,
            ':userType' => $userType
        ]);
    }

    public function getUserByUsername(string $userName) {
        // Retrieve one user by username.
        $sql = "SELECT * FROM Users WHERE userName = :userName LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':userName' => $userName]);
        return $stmt->fetch();
    }

    // Add a new property listing to the database.
    public function addProperty(string $title, string $propertyType, string $address, string $city, float $price, string $status, int $agentId): bool {
        $sql = "INSERT INTO Properties (title, propertyType, address, city, price, status, agentId)
                VALUES (:title, :propertyType, :address, :city, :price, :status, :agentId)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':propertyType' => $propertyType,
            ':address' => $address,
            ':city' => $city,
            ':price' => $price,
            ':status' => $status,
            ':agentId' => $agentId
        ]);
    }

    // Retrieve all property listings
    public function getAllProperties(): array {
        // TODO: Optionally replace this with the PropertyListingView.
        $sql = "SELECT p.*, u.userName AS agentName
                FROM Properties p
                JOIN Users u ON p.agentId = u.userId
                ORDER BY p.propertyId DESC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    public function getPropertyById(int $propertyId) {
        $sql = "SELECT p.*, u.userName AS agentName
                FROM Properties p
                JOIN Users u ON p.agentId = u.userId
                WHERE p.propertyId = :propertyId";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':propertyId' => $propertyId]);
        return $stmt->fetch();
    }

    // Add a new inquiry to the database.
    public function addInquiry(int $userId, int $propertyId, string $message): bool {
        $sql = "INSERT INTO Inquiries (userId, propertyId, message, inquiryDate)
                VALUES (:userId, :propertyId, :message, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':userId' => $userId,
            ':propertyId' => $propertyId,
            ':message' => $message
        ]);
    }

    // Get user details along with their inquiries, favorites, and transactions.
    public function getUserDetails(int $userId) {
        // Get user info
        $sql = "SELECT * FROM Users WHERE userId = :userId";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $user = $stmt->fetch();

        if (!$user) return null;

        // Get inquiries
        $sql = "SELECT i.*, p.title AS propertyTitle FROM Inquiries i JOIN Properties p ON i.propertyId = p.propertyId WHERE i.userId = :userId";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $user['inquiries'] = $stmt->fetchAll();

        // Get favorites
        $sql = "SELECT f.*, p.title AS propertyTitle FROM Favorites f JOIN Properties p ON f.propertyId = p.propertyId WHERE f.userId = :userId";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $user['favorites'] = $stmt->fetchAll();

        // Get transactions
        $sql = "SELECT t.*, p.title AS propertyTitle FROM Transactions t JOIN Properties p ON t.propertyId = p.propertyId WHERE t.userId = :userId";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        $user['transactions'] = $stmt->fetchAll();

        return $user;
    }

    public function getPropertiesByCity(string $city): array {
        $sql = "SELECT p.*, u.userName AS agentName
            FROM Properties p
            JOIN Users u ON p.agentId = u.userId
            WHERE p.city = :city";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':city' => $city]);
        return $stmt->fetchAll();
    }
}
?>
