<?php
require_once '../includes/config.php';
checkRole('farmer');
$farmerId = $_SESSION['user_id'];

$listings = $conn->query("
    SELECT l.*, 
    (SELECT COUNT(*) FROM bids b WHERE b.listing_id=l.id) as bid_count,
    (SELECT MAX(b.bid_amount) FROM bids b WHERE b.listing_id=l.id) as highest_bid
    FROM listings l 
    WHERE l.farmer_id = $farmerId 
    ORDER BY l.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Listings | APMC e-Trading</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="sidebar-brand-name">🌾 APMC</div><div class="sidebar-brand-sub">e-Trading System</div></div>
        <div class="sidebar-user"><div class="sidebar-avatar">👨‍🌾</div><div><div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div><div class="sidebar-user-role">Farmer</div></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><span class="sidebar-nav-icon">📊</span> Dashboard</a>
            <a href="add-listing.php"><span class="sidebar-nav-icon">➕</span> Add Listing</a>
            <a href="my-listings.php" class="active"><span class="sidebar-nav-icon">📦</span> My Listings</a>
            <a href="my-bids.php"><span class="sidebar-nav-icon">🔨</span> Bids Received</a>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php"><span class="sidebar-nav-icon">👤</span> My Profile</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">My Listings</div>
            <div class="topbar-right"><a href="add-listing.php" class="btn-add">➕ Add New Listing</a></div>
        </div>
        <div class="page-content">
            <div class="data-table-wrap">
                <table>
                    <thead>
                        <tr><th>Produce</th><th>Category</th><th>Quantity</th><th>Base Price</th><th>Highest Bid</th><th>Bids</th><th>Status</th><th>Ends</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($listings->num_rows === 0): ?>
                        <tr><td colspan="9" style="text-align:center;padding:40px;color:#aaa;">No listings yet. <a href="add-listing.php">Add your first →</a></td></tr>
                    <?php else: ?>
                    <?php while ($l = $listings->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($l['produce_name']) ?></strong></td>
                        <td><?= htmlspecialchars($l['category']) ?></td>
                        <td><?= $l['quantity'] ?> <?= $l['unit'] ?></td>
                        <td>₹<?= number_format($l['base_price'],2) ?></td>
                        <td><?= $l['highest_bid'] ? '₹'.number_format($l['highest_bid'],2) : '—' ?></td>
                        <td><?= $l['bid_count'] ?></td>
                        <td><span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span></td>
                        <td><?= date('d M Y', strtotime($l['bid_end_time'])) ?></td>
                        <td><a href="view-bids.php?id=<?= $l['id'] ?>" class="btn-sm btn-sm-blue">View Bids</a></td>
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
