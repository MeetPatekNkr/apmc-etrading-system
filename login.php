<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : ($_SESSION['role'] === 'farmer' ? 'farmer/dashboard.php' : 'trader/dashboard.php'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($conn, $_POST['role']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        if (!$user['is_approved'] && $role !== 'admin') {
            $error = 'Your account is pending APMC approval. Please wait for confirmation.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            redirect($role === 'admin' ? 'admin/dashboard.php' : ($role === 'farmer' ? 'farmer/dashboard.php' : 'trader/dashboard.php'));
        }
    } else {
        $error = 'Invalid email, password, or role selection.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | APMC e-Trading System</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-panel-left">
        <div class="auth-brand-logo">🌾</div>
        <h2>APMC<br>e-Trading<br>System</h2>
        <p>Gujarat's premier digital agricultural trading platform — transparent, fair, and secure.</p>
        <div class="auth-benefits">
            <div class="auth-benefit">
                <div class="auth-benefit-icon">💰</div>
                <span>Competitive bidding for fair prices</span>
            </div>
            <div class="auth-benefit">
                <div class="auth-benefit-icon">🔍</div>
                <span>100% transparent transactions</span>
            </div>
            <div class="auth-benefit">
                <div class="auth-benefit-icon">📱</div>
                <span>Access from anywhere, anytime</span>
            </div>
            <div class="auth-benefit">
                <div class="auth-benefit-icon">🛡️</div>
                <span>Verified farmers & traders only</span>
            </div>
        </div>
    </div>
    <div class="auth-panel-right">
        <div class="auth-form-wrap">
            <a href="index.php" class="auth-back">← Back to Home</a>
            <h2>Welcome Back</h2>
            <p>Login to access your APMC e-Trading dashboard</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Select Your Role</label>
                    <div class="role-selector">
                        <div class="role-option <?= (!isset($_POST['role']) || $_POST['role'] === 'farmer') ? 'selected' : '' ?>" onclick="selectRole('farmer', this)">
                            <div class="role-option-icon">👨‍🌾</div>
                            <div class="role-option-name">Farmer</div>
                        </div>
                        <div class="role-option <?= (isset($_POST['role']) && $_POST['role'] === 'trader') ? 'selected' : '' ?>" onclick="selectRole('trader', this)">
                            <div class="role-option-icon">🤝</div>
                            <div class="role-option-name">Trader</div>
                        </div>
                        <div class="role-option <?= (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : '' ?>" onclick="selectRole('admin', this)">
                            <div class="role-option-icon">🏛️</div>
                            <div class="role-option-name">APMC Officer</div>
                        </div>
                    </div>
                    <input type="hidden" name="role" id="role" value="<?= htmlspecialchars($_POST['role'] ?? 'farmer') ?>">
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-submit">Login to Dashboard</button>
            </form>

            <div class="auth-switch">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
            <div class="auth-switch" style="margin-top:8px;">
                <small style="color:#aaa;">Admin demo: admin@apmc.gov.in / Admin@123</small>
            </div>
        </div>
    </div>
</div>
<script>
function selectRole(role, el) {
    document.querySelectorAll('.role-option').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('role').value = role;
}
</script>
</body>
</html>
