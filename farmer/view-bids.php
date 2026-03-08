<?php
require_once '../includes/config.php';
checkRole('farmer');
$farmerId = $_SESSION['user_id'];
$listingId = intval($_GET['id'] ?? 0);

$listing = $conn->query("SELECT * FROM listings WHERE id = $listingId AND farmer_id = $farmerId")->fetch_assoc();
if (!$listing) redirect('my-listings.php');

$bids = $conn->query("
    SELECT b.*, u.full_name, u.phone, u.district 
    FROM bids b 
    JOIN users u ON b.trader_id = u.id 
    WHERE b.listing_id = $listingId 
    ORDER BY b.bid_amount DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Bids | APMC e-Trading</title>
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
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">Bids for: <?= htmlspecialchars($listing['produce_name']) ?></div>
            <div class="topbar-right"><a href="my-listings.php" style="color:#666;font-size:14px;text-decoration:none;">← Back to Listings</a></div>
        </div>
        <div class="page-content">
            <!-- Listing Summary -->
            <div class="stat-card" style="max-width:600px;margin-bottom:24px;gap:24px;">
                <div>
                    <div style="font-size:13px;color:#aaa;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Listing Details</div>
                    <div style="font-size:20px;font-weight:700;color:var(--green-dark);"><?= htmlspecialchars($listing['produce_name']) ?></div>
                    <div style="font-size:14px;color:#666;margin-top:4px;">
                        <?= $listing['quantity'] ?> <?= $listing['unit'] ?> • Base Price: ₹<?= number_format($listing['base_price'],2) ?> 
                        • Status: <span class="badge badge-<?= $listing['status'] ?>"><?= ucfirst($listing['status']) ?></span>
                    </div>
                </div>
            </div>

            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">All Bids Received (<?= $bids->num_rows ?>)</div>
                </div>
                <table>
                    <thead>
                        <tr><th>Rank</th><th>Trader Name</th><th>District</th><th>Bid Amount</th><th>Time</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($bids->num_rows === 0): ?>
                        <tr><td colspan="6" style="text-align:center;padding:40px;color:#aaa;">No bids received yet for this listing.</td></tr>
                    <?php else: ?>
                    <?php $rank = 1; while ($b = $bids->fetch_assoc()): ?>
                    <tr style="<?= $rank === 1 ? 'background:#f0fdf4;' : '' ?>">
                        <td>
                            <?= $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : $rank)) ?>
                        </td>
                        <td><strong><?= htmlspecialchars($b['full_name']) ?></strong></td>
                        <td><?= htmlspecialchars($b['district'] ?: 'N/A') ?></td>
                        <td style="font-size:18px;font-weight:700;color:var(--green-dark);">₹<?= number_format($b['bid_amount'],2) ?></td>
                        <td><?= date('d M Y H:i', strtotime($b['created_at'])) ?></td>
                        <td><span class="badge badge-<?= $b['status'] === 'won' ? 'approved' : ($b['status'] === 'lost' ? 'expired' : 'active') ?>"><?= ucfirst($b['status']) ?></span></td>
                    </tr>
                    <?php $rank++; endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($listing['status'] === 'active'): ?>
            <div class="alert alert-info" style="margin-top:16px;">ℹ️ The APMC Officer will close bidding and award the sale to the highest bidder at the end of the auction period.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
