<?php
$message = '';
require_once 'config/config.php'; // Ensures session_start and config
require_once 'classes/RealEstateDatabase.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new RealEstateDatabase();
    $userName = trim($_POST['userName'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = $db->getUserByUsername($userName);
    // Securely verify password
    if ($user && password_verify($password, $user['passwordHash'])) {
        unset($user['passwordHash']); // Don't store hash in session
        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $message = 'Invalid username or password.';
    }
}
?>
<?php include 'includes/header.php'; ?>
<h2>Login</h2>
<?php if ($message): ?>
    <p class="error"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Username</label>
    <input type="text" name="userName" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
</form>
<?php include 'includes/footer.php'; ?>
