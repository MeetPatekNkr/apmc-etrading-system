<?php
require_once '../includes/config.php';
checkRole('farmer');
$farmerId = $_SESSION['user_id'];

// All bids received on farmer's listings
$bids = $conn->query("
    SELECT b.*, l.produce_name, l.quantity, l.unit, l.base_price, l.status as listing_status,
           u.full_name as trader_name, u.phone as trader_phone, u.district as trader_district
    FROM bids b
    JOIN listings l ON b.listing_id = l.id
    JOIN users u ON b.trader_id = u.id
    WHERE l.farmer_id = $farmerId
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bids Received | APMC e-Trading</title>
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
            <a href="my-bids.php" class="active"><span class="sidebar-nav-icon">🔨</span> Bids Received</a>
            <div class="sidebar-section-label">Account</div>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php"><span class="sidebar-nav-icon">👤</span> My Profile</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">Bids Received</div>
            <div class="topbar-right">
                <span style="font-size:14px;color:#666;">Total Bids: <strong><?= $bids->num_rows ?></strong></span>
            </div>
        </div>

        <div class="page-content">

            <?php if ($bids->num_rows === 0): ?>
                <div class="alert alert-info">
                    🔨 Koi bid abhi nahi mili. Pehle <a href="add-listing.php">produce listing add karo</a> aur APMC approval ke baad traders bid karenge.
                </div>
            <?php else: ?>

            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">🔨 All Bids on Your Listings</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produce</th>
                            <th>Trader Name</th>
                            <th>District</th>
                            <th>Bid Amount</th>
                            <th>Base Price</th>
                            <th>Status</th>
                            <th>Bid Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $sr = 1; while ($b = $bids->fetch_assoc()): ?>
                    <tr style="<?= $b['status'] === 'won' ? 'background:#f0fdf4;' : '' ?>">
                        <td><?= $sr++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($b['produce_name']) ?></strong><br>
                            <small style="color:#aaa;"><?= $b['quantity'] ?> <?= $b['unit'] ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($b['trader_name']) ?></strong><br>
                            <small style="color:#aaa;">📞 <?= htmlspecialchars($b['trader_phone']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($b['trader_district'] ?: 'N/A') ?></td>
                        <td style="font-size:18px;font-weight:700;color:var(--green-dark);">
                            ₹<?= number_format($b['bid_amount'], 2) ?>
                        </td>
                        <td style="color:#888;">₹<?= number_format($b['base_price'], 2) ?></td>
                        <td>
                            <?php if ($b['status'] === 'won'): ?>
                                <span class="badge badge-approved">🏆 Won</span>
                            <?php elseif ($b['status'] === 'lost'): ?>
                                <span class="badge badge-expired">Lost</span>
                            <?php else: ?>
                                <span class="badge badge-active">Active</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;color:#888;"><?= date('d M Y H:i', strtotime($b['created_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
