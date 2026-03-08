<?php
require_once '../includes/config.php';
checkRole('farmer');

$farmerId = $_SESSION['user_id'];
$farmerName = $_SESSION['user_name'];

// Counts
$totalListings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE farmer_id = $farmerId")->fetch_assoc()['c'];
$activeListings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE farmer_id = $farmerId AND status = 'active'")->fetch_assoc()['c'];
$soldListings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE farmer_id = $farmerId AND status = 'sold'")->fetch_assoc()['c'];
$totalRevenue = $conn->query("SELECT COALESCE(SUM(final_amount),0) as r FROM transactions WHERE farmer_id = $farmerId")->fetch_assoc()['r'];

// Recent listings
$listings = $conn->query("SELECT l.*, (SELECT COUNT(*) FROM bids b WHERE b.listing_id=l.id) as bid_count FROM listings l WHERE l.farmer_id = $farmerId ORDER BY l.created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard | APMC e-Trading</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-name">🌾 APMC</div>
            <div class="sidebar-brand-sub">e-Trading System</div>
        </div>
        <div class="sidebar-user">
            <div class="sidebar-avatar">👨‍🌾</div>
            <div>
                <div class="sidebar-user-name"><?= htmlspecialchars($farmerName) ?></div>
                <div class="sidebar-user-role">Farmer</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="dashboard.php" class="active">
                <span class="sidebar-nav-icon">📊</span> Dashboard
            </a>
            <a href="add-listing.php">
                <span class="sidebar-nav-icon">➕</span> Add Listing
            </a>
            <a href="my-listings.php">
                <span class="sidebar-nav-icon">📦</span> My Listings
            </a>
            <a href="my-bids.php">
                <span class="sidebar-nav-icon">🔨</span> Bids Received
            </a>
            <div class="sidebar-section-label">Account</div>
            <a href="transactions.php">
                <span class="sidebar-nav-icon">💰</span> Transactions
            </a>
            <a href="profile.php">
                <span class="sidebar-nav-icon">👤</span> My Profile
            </a>
        </nav>
        <div class="sidebar-logout">
            <a href="../logout.php">🚪 Logout</a>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">Farmer Dashboard</div>
            <div class="topbar-right">
                <button class="notif-btn">🔔</button>
                <span style="font-size:14px;color:#666;">Welcome, <?= htmlspecialchars(explode(' ', $farmerName)[0]) ?> 👋</span>
            </div>
        </div>

        <div class="page-content">
            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-card-icon green">📦</div>
                    <div>
                        <div class="stat-card-num"><?= $totalListings ?></div>
                        <div class="stat-card-label">Total Listings</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon amber">⚡</div>
                    <div>
                        <div class="stat-card-num"><?= $activeListings ?></div>
                        <div class="stat-card-label">Active Listings</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon blue">✅</div>
                    <div>
                        <div class="stat-card-num"><?= $soldListings ?></div>
                        <div class="stat-card-label">Sold Items</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon green">💰</div>
                    <div>
                        <div class="stat-card-num">₹<?= number_format($totalRevenue, 0) ?></div>
                        <div class="stat-card-label">Total Revenue</div>
                    </div>
                </div>
            </div>

            <!-- Quick Action -->
            <div style="margin-bottom: 24px;">
                <a href="add-listing.php" class="btn-add" style="display:inline-flex;align-items:center;gap:8px;font-size:15px;padding:12px 24px;">
                    ➕ List New Produce
                </a>
            </div>

            <!-- Listings Table -->
            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">My Recent Listings</div>
                    <a href="my-listings.php" class="btn-add">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Produce</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Base Price</th>
                            <th>Bids</th>
                            <th>Status</th>
                            <th>Bid Ends</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($listings->num_rows === 0): ?>
                        <tr><td colspan="8" style="text-align:center;color:#aaa;padding:40px;">No listings yet. <a href="add-listing.php">Add your first listing →</a></td></tr>
                    <?php else: ?>
                        <?php while ($l = $listings->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($l['produce_name']) ?></strong></td>
                            <td><?= htmlspecialchars($l['category']) ?></td>
                            <td><?= $l['quantity'] ?> <?= $l['unit'] ?></td>
                            <td>₹<?= number_format($l['base_price'], 2) ?></td>
                            <td><span class="badge badge-active"><?= $l['bid_count'] ?> bids</span></td>
                            <td><span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span></td>
                            <td><?= date('d M Y H:i', strtotime($l['bid_end_time'])) ?></td>
                            <td class="action-btns">
                                <a href="view-bids.php?id=<?= $l['id'] ?>" class="btn-sm btn-sm-blue">View Bids</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
