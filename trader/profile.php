<?php
require_once '../includes/config.php';
checkRole('trader');
$traderId = $_SESSION['user_id'];

$success = '';
$error = '';

$user = $conn->query("SELECT * FROM users WHERE id = $traderId")->fetch_assoc();

// Profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name   = sanitize($conn, $_POST['full_name']);
    $phone       = sanitize($conn, $_POST['phone']);
    $village     = sanitize($conn, $_POST['village']);
    $district    = sanitize($conn, $_POST['district']);
    $address     = sanitize($conn, $_POST['address']);
    $license     = sanitize($conn, $_POST['license']);

    $stmt = $conn->prepare("UPDATE users SET full_name=?, phone=?, village=?, district=?, address=?, license_number=? WHERE id=?");
    $stmt->bind_param("ssssssi", $full_name, $phone, $village, $district, $address, $license, $traderId);
    if ($stmt->execute()) {
        $_SESSION['user_name'] = $full_name;
        $success = 'Profile updated successfully!';
        $user = $conn->query("SELECT * FROM users WHERE id = $traderId")->fetch_assoc();
    } else {
        $error = 'Update failed. Please try again.';
    }
}

// Password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hashed' WHERE id=$traderId");
        $success = 'Password changed successfully!';
    }
}

// Trader stats
$totalBids   = $conn->query("SELECT COUNT(*) as c FROM bids WHERE trader_id=$traderId")->fetch_assoc()['c'];
$wonBids     = $conn->query("SELECT COUNT(*) as c FROM bids WHERE trader_id=$traderId AND status='won'")->fetch_assoc()['c'];
$totalSpent  = $conn->query("SELECT COALESCE(SUM(final_amount),0) as r FROM transactions WHERE trader_id=$traderId")->fetch_assoc()['r'];

$districts = ['Ahmedabad','Surat','Vadodara','Rajkot','Bhavnagar','Anand','Kheda','Mehsana','Patan','Gandhinagar','Junagadh','Amreli','Jamnagar','Kutch','Banaskantha','Sabarkantha','Arvalli','Mahisagar','Chhota Udaipur','Dahod','Panchmahal','Narmada','Bharuch','Tapi','Navsari','Valsad','Dang'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | APMC e-Trading</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-name">🌾 APMC</div>
            <div class="sidebar-brand-sub">e-Trading System</div>
        </div>
        <div class="sidebar-user">
            <div class="sidebar-avatar">🤝</div>
            <div>
                <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                <div class="sidebar-user-role">Trader</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="dashboard.php"><span class="sidebar-nav-icon">📊</span> Dashboard</a>
            <a href="browse-listings.php"><span class="sidebar-nav-icon">🌾</span> Browse Listings</a>
            <a href="my-bids.php"><span class="sidebar-nav-icon">🔨</span> My Bids</a>
            <div class="sidebar-section-label">Account</div>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php" class="active"><span class="sidebar-nav-icon">👤</span> Profile</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">My Profile</div>
        </div>

        <div class="page-content">

            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

                <!-- Profile Edit -->
                <div class="form-card" style="grid-column:1/3;">
                    <!-- Header -->
                    <div style="display:flex;align-items:center;gap:20px;margin-bottom:28px;padding-bottom:20px;border-bottom:2px solid var(--cream-dark);">
                        <div style="width:80px;height:80px;background:var(--amber);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:40px;flex-shrink:0;">🤝</div>
                        <div>
                            <div style="font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--green-dark);"><?= htmlspecialchars($user['full_name']) ?></div>
                            <div style="color:#888;font-size:14px;margin-top:4px;">📧 <?= htmlspecialchars($user['email']) ?></div>
                            <div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;">
                                <span class="badge badge-approved">✅ Verified Trader</span>
                                <?php if ($user['license_number']): ?>
                                    <span class="badge badge-active">License: <?= htmlspecialchars($user['license_number']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Quick Stats -->
                        <div style="margin-left:auto;display:flex;gap:24px;text-align:center;">
                            <div>
                                <div style="font-family:var(--font-display);font-size:24px;font-weight:700;color:var(--green-dark);"><?= $totalBids ?></div>
                                <div style="font-size:12px;color:#888;">Total Bids</div>
                            </div>
                            <div>
                                <div style="font-family:var(--font-display);font-size:24px;font-weight:700;color:#16a34a;"><?= $wonBids ?></div>
                                <div style="font-size:12px;color:#888;">Bids Won</div>
                            </div>
                            <div>
                                <div style="font-family:var(--font-display);font-size:24px;font-weight:700;color:#2563eb;">₹<?= number_format($totalSpent,0) ?></div>
                                <div style="font-size:12px;color:#888;">Total Spent</div>
                            </div>
                        </div>
                    </div>

                    <h3 style="margin-bottom:20px;">✏️ Edit Profile</h3>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Mobile Number *</label>
                                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f5f5f5;cursor:not-allowed;">
                            <small style="color:#aaa;font-size:12px;">Email cannot be changed.</small>
                        </div>
                        <div class="form-group">
                            <label>Trade License Number</label>
                            <input type="text" name="license" value="<?= htmlspecialchars($user['license_number']??'') ?>" placeholder="Your trade license number">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>City / Town</label>
                                <input type="text" name="village" value="<?= htmlspecialchars($user['village']??'') ?>" placeholder="Your city">
                            </div>
                            <div class="form-group">
                                <label>District</label>
                                <select name="district">
                                    <option value="">Select District</option>
                                    <?php foreach ($districts as $d): ?>
                                        <option value="<?= $d ?>" <?= ($user['district']??'') === $d ? 'selected' : '' ?>><?= $d ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Full Address</label>
                            <textarea name="address" rows="3" placeholder="Business address..."><?= htmlspecialchars($user['address']??'') ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn-submit" style="max-width:250px;">💾 Save Changes</button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="form-card">
                    <h3>🔒 Change Password</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Current Password *</label>
                            <input type="password" name="current_password" placeholder="Enter current password" required>
                        </div>
                        <div class="form-group">
                            <label>New Password *</label>
                            <input type="password" name="new_password" placeholder="Min 6 characters" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password *</label>
                            <input type="password" name="confirm_password" placeholder="Repeat new password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn-submit" style="max-width:200px;background:#475569;">🔒 Update Password</button>
                    </form>
                </div>

                <!-- Account Info -->
                <div class="form-card">
                    <h3>ℹ️ Account Information</h3>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:8px;">
                        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                            <span style="color:#888;font-size:14px;">Trader ID</span>
                            <span style="font-weight:600;">#<?= str_pad($user['id'],5,'0',STR_PAD_LEFT) ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                            <span style="color:#888;font-size:14px;">Role</span>
                            <span class="badge badge-pending">Trader</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                            <span style="color:#888;font-size:14px;">Account Status</span>
                            <span class="badge badge-approved">✅ Approved</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                            <span style="color:#888;font-size:14px;">License No.</span>
                            <span style="font-weight:600;"><?= htmlspecialchars($user['license_number']??'Not provided') ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:12px 0;">
                            <span style="color:#888;font-size:14px;">Registered On</span>
                            <span style="font-weight:600;"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>
