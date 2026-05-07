<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'classes/RealEstateDatabase.php';

$db = new RealEstateDatabase();
$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$property = $db->getPropertyById($propertyId);
$message = '';

// Handle Delete Property (Agent)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_property'])) {
    requireRole(['agent']);
    // Only allow if agent owns the property
    if (isset($_SESSION['user']) && $property && $_SESSION['user']['userId'] == $property['agentId']) {
        if ($db->deleteProperty($propertyId)) {
            header('Location: properties.php');
            exit;
        } else {
            $message = 'Error deleting property.';
        }
    } else {
        $message = 'Unauthorized action.';
    }
}

// Handle Save to Favorites (Buyer/Renter)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_favorite'])) {
    requireRole(['buyer', 'renter']);
    if (isset($_SESSION['user']) && $property) {
        $userId = $_SESSION['user']['userId'];
        try {
            $db->addFavorite($userId, $propertyId);
            $message = 'Property saved to favorites!';
        } catch (Throwable $e) {
            $message = 'Error saving favorite: ' . $e->getMessage();
        }
    }
}

?>
<?php include 'includes/header.php'; ?>
<h2>Property Details</h2>

<?php if ($message): ?>
    <p class="error"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<?php if (!$property): ?>
    <p class="error">Property not found.</p>
<?php else: ?>
    <div class="card">
        <h3><?= htmlspecialchars($property['title']) ?></h3>
        <p><strong>Type:</strong> <?= htmlspecialchars($property['propertyType']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($property['address']) ?></p>
        <p><strong>City:</strong> <?= htmlspecialchars($property['city']) ?></p>
        <p><strong>Price:</strong> $<?= htmlspecialchars($property['price']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($property['status']) ?></p>
        <p><strong>Agent:</strong> <?= htmlspecialchars($property['agentName']) ?></p>
    </div>

    <?php if (isset($_SESSION['user'])): ?>
        <?php if ($_SESSION['user']['userType'] === 'agent' && $_SESSION['user']['userId'] == $property['agentId']): ?>
            <!-- Delete Property Button -->
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this property?');">
                <button style="color: white;" type="submit" name="delete_property" class="error">Delete Property</button>
            </form>
        <?php elseif (in_array($_SESSION['user']['userType'], ['buyer', 'renter'], true)): ?>
            <div style="display: flex; gap: 16px; align-items: center; margin-top: 16px;">
                <!-- Save to Favorites Button -->
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="save_favorite">Save to Favorites</button>
                </form>
                <!-- Submit Inquiry Button -->
                <form action="submit_inquiry.php" method="get" style="margin: 0;">
                    <input type="hidden" name="propertyId" value="<?= (int)$property['propertyId'] ?>">
                    <button type="submit">Submit Inquiry</button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>