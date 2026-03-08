<?php
require_once '../includes/config.php';
checkRole('farmer');
$farmerId = $_SESSION['user_id'];

$success = '';
$error = '';

// Fetch current profile
$user = $conn->query("SELECT * FROM users WHERE id = $farmerId")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($conn, $_POST['full_name']);
    $phone     = sanitize($conn, $_POST['phone']);
    $village   = sanitize($conn, $_POST['village']);
    $district  = sanitize($conn, $_POST['district']);
    $address   = sanitize($conn, $_POST['address']);
    $aadhar    = sanitize($conn, $_POST['aadhar']);

    $stmt = $conn->prepare("UPDATE users SET full_name=?, phone=?, village=?, district=?, address=?, aadhar_number=? WHERE id=?");
    $stmt->bind_param("ssssssi", $full_name, $phone, $village, $district, $address, $aadhar, $farmerId);
    if ($stmt->execute()) {
        $_SESSION['user_name'] = $full_name;
        $success = 'Profile updated successfully!';
        $user = $conn->query("SELECT * FROM users WHERE id = $farmerId")->fetch_assoc();
    } else {
        $error = 'Update failed. Please try again.';
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current  = $_POST['current_password'];
    $new      = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hashed' WHERE id=$farmerId");
        $success = 'Password changed successfully!';
    }
}

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
            <div class="sidebar-avatar">👨‍🌾</div>
            <div>
                <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                <div class="sidebar-user-role">Farmer</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="dashboard.php"><span class="sidebar-nav-icon">📊</span> Dashboard</a>
            <a href="add-listing.php"><span class="sidebar-nav-icon">➕</span> Add Listing</a>
            <a href="my-listings.php"><span class="sidebar-nav-icon">📦</span> My Listings</a>
            <a href="my-bids.php"><span class="sidebar-nav-icon">🔨</span> Bids Received</a>
            <div class="sidebar-section-label">Account</div>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php" class="active"><span class="sidebar-nav-icon">👤</span> My Profile</a>
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

                <!-- Profile Info Card -->
                <div class="form-card" style="grid-column:1/3;">
                    <!-- Profile Header -->
                    <div style="display:flex;align-items:center;gap:20px;margin-bottom:28px;padding-bottom:20px;border-bottom:2px solid var(--cream-dark);">
                        <div style="width:80px;height:80px;background:var(--green-mid);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:40px;flex-shrink:0;">
                            👨‍🌾
                        </div>
                        <div>
                            <div style="font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--green-dark);"><?= htmlspecialchars($user['full_name']) ?></div>
                            <div style="color:#888;font-size:14px;margin-top:4px;">📧 <?= htmlspecialchars($user['email']) ?></div>
                            <div style="margin-top:8px;display:flex;gap:10px;">
                                <span class="badge badge-approved">✅ Verified Farmer</span>
                                <span class="badge badge-active">Gujarat APMC</span>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Profile Form -->
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
                            <small style="color:#aaa;font-size:12px;">Email cannot be changed. Contact APMC for assistance.</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Village / Town</label>
                                <input type="text" name="village" value="<?= htmlspecialchars($user['village'] ?? '') ?>" placeholder="Your village name">
                            </div>
                            <div class="form-group">
                                <label>District</label>
                                <select name="district">
                                    <option value="">Select District</option>
                                    <?php foreach ($districts as $d): ?>
                                        <option value="<?= $d ?>" <?= ($user['district'] ?? '') === $d ? 'selected' : '' ?>><?= $d ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Aadhar Number</label>
                            <input type="text" name="aadhar" value="<?= htmlspecialchars($user['aadhar_number'] ?? '') ?>" maxlength="12" placeholder="12-digit Aadhar number">
                        </div>
                        <div class="form-group">
                            <label>Full Address</label>
                            <textarea name="address" rows="3" placeholder="Your complete address..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
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
                    <div style="display:flex;flex-direction:column;gap:16px;margin-top:8px;">
                        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                            <span style="color:#888;font-size:14px;">User ID</span>
                            <span style="font-weight:600;">#<?= str_pad($user['id'],5,'0',STR_PAD_LEFT) ?></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                            <span style="color:#888;font-size:14px;">Role</span>
                            <span class="badge badge-active">Farmer</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                            <span style="color:#888;font-size:14px;">Account Status</span>
                            <span class="badge badge-approved">✅ Approved</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                            <span style="color:#888;font-size:14px;">State</span>
                            <span style="font-weight:600;">Gujarat</span>
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
