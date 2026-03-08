<?php
require_once '../includes/config.php';
checkRole('farmer');

$farmerId = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produce_name = sanitize($conn, $_POST['produce_name']);
    $category = sanitize($conn, $_POST['category']);
    $quantity = floatval($_POST['quantity']);
    $unit = sanitize($conn, $_POST['unit']);
    $base_price = floatval($_POST['base_price']);
    $description = sanitize($conn, $_POST['description']);
    $location = sanitize($conn, $_POST['location']);
    $bid_end_time = sanitize($conn, $_POST['bid_end_time']);

    if ($produce_name && $category && $quantity > 0 && $base_price > 0 && $bid_end_time) {
        $stmt = $conn->prepare("INSERT INTO listings (farmer_id, produce_name, category, quantity, unit, base_price, description, location, bid_end_time, status) VALUES (?,?,?,?,?,?,?,?,?,'pending')");
        $stmt->bind_param("isssdssss", $farmerId, $produce_name, $category, $quantity, $unit, $base_price, $description, $location, $bid_end_time);
        if ($stmt->execute()) {
            $success = 'Listing submitted successfully! APMC Officer will review and activate it.';
        } else {
            $error = 'Failed to add listing. Try again.';
        }
    } else {
        $error = 'Please fill all required fields correctly.';
    }
}

$categories = ['Grains & Cereals','Cotton','Vegetables','Fruits','Pulses','Oilseeds','Spices','Sugarcane','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Listing | APMC e-Trading</title>
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
            <a href="add-listing.php" class="active"><span class="sidebar-nav-icon">➕</span> Add Listing</a>
            <a href="my-listings.php"><span class="sidebar-nav-icon">📦</span> My Listings</a>
            <a href="my-bids.php"><span class="sidebar-nav-icon">🔨</span> Bids Received</a>
            <div class="sidebar-section-label">Account</div>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php"><span class="sidebar-nav-icon">👤</span> My Profile</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">Add New Produce Listing</div>
            <div class="topbar-right">
                <a href="my-listings.php" style="color:#666;text-decoration:none;font-size:14px;">← Back to Listings</a>
            </div>
        </div>

        <div class="page-content">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <div class="form-card">
                <h3>📋 Produce Listing Details</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Produce Name *</label>
                            <input type="text" name="produce_name" placeholder="e.g., Wheat, Cotton, Onion" required value="<?= htmlspecialchars($_POST['produce_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Quantity *</label>
                            <input type="number" name="quantity" placeholder="e.g., 500" step="0.01" min="0.01" required value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Unit *</label>
                            <select name="unit" required>
                                <option value="Quintal" selected>Quintal</option>
                                <option value="Kg">Kilogram (Kg)</option>
                                <option value="Ton">Metric Ton</option>
                                <option value="Bag">Bag</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Base / Minimum Price (₹ per unit) *</label>
                            <input type="number" name="base_price" placeholder="e.g., 2000" step="0.01" min="1" required value="<?= htmlspecialchars($_POST['base_price'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Bidding End Date & Time *</label>
                            <input type="datetime-local" name="bid_end_time" required min="<?= date('Y-m-d\TH:i') ?>" value="<?= htmlspecialchars($_POST['bid_end_time'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Location / Pickup Point</label>
                        <input type="text" name="location" placeholder="e.g., Anand APMC Yard, Village Name" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Description / Quality Details</label>
                        <textarea name="description" rows="4" placeholder="Describe quality, grade, harvest details, storage conditions..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div style="background: #fef3c7; border: 1px solid #fcd34d; border-radius: 10px; padding: 14px; margin-bottom: 20px; font-size: 14px; color: #92400e;">
                        ⚠️ <strong>Note:</strong> Your listing will be reviewed by an APMC Officer before it goes live for bidding.
                    </div>

                    <button type="submit" class="btn-submit" style="max-width:300px;">Submit Listing for Approval</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
