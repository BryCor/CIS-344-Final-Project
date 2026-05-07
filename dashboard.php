<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
requireLogin();
require_once 'classes/RealEstateDatabase.php';
$user = $_SESSION['user'];
$db = new RealEstateDatabase();
$showDetails = isset($_GET['show_details']) && in_array($user['userType'], ['buyer', 'renter']);
?>
<?php include 'includes/header.php'; ?>
<h2>Dashboard</h2>

<div class="card">
    <p><strong>Welcome:</strong> <?= htmlspecialchars($user['userName']) ?></p>
    <p><strong>Role:</strong> <?= htmlspecialchars($user['userType']) ?></p>
</div>

<?php if ($user['userType'] === 'agent'): ?>
    <div class="card">
        <h3>Agent Actions</h3>
        <a href="add_property.php">Add Property</a>
    </div>
<?php endif; ?>

<div class="card">
    <h3>Common Actions</h3>
    <a href="properties.php">Browse Properties</a>
</div>

// Show buyer/renter details if requested
<?php if (in_array($user['userType'], ['buyer', 'renter'])): ?>
    <div class="card">
        <h3>Buyer/Renter Actions</h3>
        <a href="dashboard.php?show_details=1">View Buyer/Renter Details</a>
    </div>
    <?php if ($showDetails): ?>
        <?php $details = $db->getUserDetails($user['userId']); ?>
        <div class="card">
            <h3>Your Info</h3>
            <p><strong>Username:</strong> <?= htmlspecialchars($details['userName']) ?></p>
            <p><strong>Contact Info:</strong> <?= htmlspecialchars($details['contactInfo']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($details['userType']) ?></p>
        </div>
        <div class="card">
            <h3>Your Favorite Listings</h3>
            <?php if (!empty($details['favorites'])): ?>
                <ul>
                <?php foreach ($details['favorites'] as $fav): ?>
                    <li>
                        <?= htmlspecialchars($fav['propertyTitle']) ?> (Saved: <?= htmlspecialchars($fav['savedDate']) ?>)
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No favorites yet.</p>
            <?php endif; ?>
        </div>
        <div class="card">
            <h3>Your Inquiries</h3>
            <?php if (!empty($details['inquiries'])): ?>
                <ul>
                <?php foreach ($details['inquiries'] as $inq): ?>
                    <li>
                        On <strong><?= htmlspecialchars($inq['propertyTitle']) ?></strong>: "<?= htmlspecialchars($inq['message']) ?>" (<?= htmlspecialchars($inq['inquiryDate']) ?>)
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No inquiries yet.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
