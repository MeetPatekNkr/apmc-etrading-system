<?php
require_once '../includes/config.php';
checkRole('trader');
$traderId = $_SESSION['user_id'];

$filter = $_GET['filter'] ?? 'all';
$where = "b.trader_id = $traderId";
if ($filter === 'active') $where .= " AND b.status='active'";
elseif ($filter === 'won') $where .= " AND b.status='won'";
elseif ($filter === 'lost') $where .= " AND b.status='lost'";

$bids = $conn->query("
    SELECT b.*, l.produce_name, l.quantity, l.unit, l.base_price, l.status as listing_status, l.bid_end_time,
           u.full_name as farmer_name, u.village as farmer_village, u.district as farmer_district,
           (SELECT MAX(b2.bid_amount) FROM bids b2 WHERE b2.listing_id = l.id) as highest_bid
    FROM bids b
    JOIN listings l ON b.listing_id = l.id
    JOIN users u ON l.farmer_id = u.id
    WHERE $where
    ORDER BY b.created_at DESC
");

$totalBids  = $conn->query("SELECT COUNT(*) as c FROM bids WHERE trader_id=$traderId")->fetch_assoc()['c'];
$wonBids    = $conn->query("SELECT COUNT(*) as c FROM bids WHERE trader_id=$traderId AND status='won'")->fetch_assoc()['c'];
$activeBids = $conn->query("SELECT COUNT(*) as c FROM bids WHERE trader_id=$traderId AND status='active'")->fetch_assoc()['c'];
$lostBids   = $conn->query("SELECT COUNT(*) as c FROM bids WHERE trader_id=$traderId AND status='lost'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bids | APMC e-Trading</title>
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
            <a href="my-bids.php" class="active"><span class="sidebar-nav-icon">🔨</span> My Bids</a>
            <div class="sidebar-section-label">Account</div>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php"><span class="sidebar-nav-icon">👤</span> Profile</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">My Bids</div>
            <div class="topbar-right">
                <a href="browse-listings.php" class="btn-add">🌾 Browse More Listings</a>
            </div>
        </div>

        <div class="page-content">

            <!-- Stats -->
            <div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
                <div class="stat-card"><div class="stat-card-icon amber">🔨</div><div><div class="stat-card-num"><?= $totalBids ?></div><div class="stat-card-label">Total Bids</div></div></div>
                <div class="stat-card"><div class="stat-card-icon green">⚡</div><div><div class="stat-card-num"><?= $activeBids ?></div><div class="stat-card-label">Active Bids</div></div></div>
                <div class="stat-card"><div class="stat-card-icon blue">🏆</div><div><div class="stat-card-num"><?= $wonBids ?></div><div class="stat-card-label">Bids Won</div></div></div>
                <div class="stat-card"><div class="stat-card-icon red">❌</div><div><div class="stat-card-num"><?= $lostBids ?></div><div class="stat-card-label">Bids Lost</div></div></div>
            </div>

            <!-- Filter Tabs -->
            <div style="display:flex;gap:10px;margin-bottom:20px;">
                <a href="my-bids.php?filter=all" class="btn-sm <?= $filter==='all'?'btn-sm-green':'btn-sm-blue' ?>" style="padding:10px 20px;">All Bids</a>
                <a href="my-bids.php?filter=active" class="btn-sm <?= $filter==='active'?'btn-sm-green':'btn-sm-blue' ?>" style="padding:10px 20px;">⚡ Active</a>
                <a href="my-bids.php?filter=won" class="btn-sm <?= $filter==='won'?'btn-sm-green':'btn-sm-blue' ?>" style="padding:10px 20px;">🏆 Won</a>
                <a href="my-bids.php?filter=lost" class="btn-sm <?= $filter==='lost'?'btn-sm-amber':'btn-sm-blue' ?>" style="padding:10px 20px;">❌ Lost</a>
            </div>

            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">🔨 Bid History</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produce</th>
                            <th>Farmer</th>
                            <th>Base Price</th>
                            <th>My Bid</th>
                            <th>Highest Bid</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Bid Ends</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($bids->num_rows === 0): ?>
                        <tr><td colspan="9" style="text-align:center;padding:40px;color:#aaa;">
                            Koi bid nahi mili. <a href="browse-listings.php">Browse listings karo aur bid lagao →</a>
                        </td></tr>
                    <?php else: ?>
                    <?php $sr=1; while ($b = $bids->fetch_assoc()):
                        $isLeading = $b['bid_amount'] >= $b['highest_bid'];
                    ?>
                    <tr style="<?= $b['status']==='won' ? 'background:#f0fdf4;' : '' ?>">
                        <td><?= $sr++ ?></td>
                        <td>
                            <strong><?= htmlspecialchars($b['produce_name']) ?></strong><br>
                            <small style="color:#aaa;"><?= $b['quantity'].' '.$b['unit'] ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($b['farmer_name']) ?><br>
                            <small style="color:#aaa;">📍 <?= htmlspecialchars($b['farmer_district']??'N/A') ?></small>
                        </td>
                        <td style="color:#888;">₹<?= number_format($b['base_price'],2) ?></td>
                        <td style="font-size:17px;font-weight:700;color:var(--green-dark);">
                            ₹<?= number_format($b['bid_amount'],2) ?>
                        </td>
                        <td style="font-weight:600;">₹<?= number_format($b['highest_bid'],2) ?></td>
                        <td>
                            <?php if ($b['status'] === 'won'): ?>
                                <span class="badge badge-approved">🏆 Winner!</span>
                            <?php elseif ($b['status'] === 'lost'): ?>
                                <span class="badge badge-expired">❌ Lost</span>
                            <?php elseif ($isLeading): ?>
                                <span class="badge badge-active">🥇 Leading</span>
                            <?php else: ?>
                                <span class="badge badge-pending">⬇ Outbid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($b['status'] === 'won'): ?>
                                <span class="badge badge-approved">✅ Won</span>
                            <?php elseif ($b['status'] === 'lost'): ?>
                                <span class="badge badge-expired">Lost</span>
                            <?php else: ?>
                                <span class="badge badge-active">Active</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;color:#888;">
                            <?= date('d M Y H:i', strtotime($b['bid_end_time'])) ?>
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
<script src="../js/countdown.js"></script>
</body>
</html>
