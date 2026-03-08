<?php
require_once '../includes/config.php';
checkRole('admin');

$filter = $_GET['listing'] ?? '';
$where = "1=1";
if ($filter) $where = "b.listing_id=".intval($filter);

$bids = $conn->query("
    SELECT b.*, l.produce_name, l.quantity, l.unit, l.base_price,
           uf.full_name as farmer_name,
           ut.full_name as trader_name, ut.phone as trader_phone, ut.district as trader_district
    FROM bids b
    JOIN listings l ON b.listing_id = l.id
    JOIN users uf ON l.farmer_id = uf.id
    JOIN users ut ON b.trader_id = ut.id
    WHERE $where
    ORDER BY b.created_at DESC
");

$totalBids  = $conn->query("SELECT COUNT(*) as c FROM bids")->fetch_assoc()['c'];
$wonBids    = $conn->query("SELECT COUNT(*) as c FROM bids WHERE status='won'")->fetch_assoc()['c'];
$activeBids = $conn->query("SELECT COUNT(*) as c FROM bids WHERE status='active'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Bids | APMC Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="sidebar-brand-name">🌾 APMC</div><div class="sidebar-brand-sub">e-Trading System</div></div>
        <div class="sidebar-user"><div class="sidebar-avatar">🏛️</div><div><div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div><div class="sidebar-user-role">APMC Officer</div></div></div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Administration</div>
            <a href="dashboard.php"><span class="sidebar-nav-icon">📊</span> Dashboard</a>
            <a href="users.php"><span class="sidebar-nav-icon">👥</span> All Users</a>
            <a href="listings.php"><span class="sidebar-nav-icon">📦</span> All Listings</a>
            <a href="bids.php" class="active"><span class="sidebar-nav-icon">🔨</span> All Bids</a>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <div class="sidebar-section-label">Reports</div>
            <a href="reports.php"><span class="sidebar-nav-icon">📈</span> Reports</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>
    <div class="main-content">
        <div class="topbar"><div class="topbar-title">All Bids</div></div>
        <div class="page-content">
            <!-- Stats -->
            <div class="stats-row" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
                <div class="stat-card"><div class="stat-card-icon amber">🔨</div><div><div class="stat-card-num"><?= $totalBids ?></div><div class="stat-card-label">Total Bids</div></div></div>
                <div class="stat-card"><div class="stat-card-icon green">⚡</div><div><div class="stat-card-num"><?= $activeBids ?></div><div class="stat-card-label">Active Bids</div></div></div>
                <div class="stat-card"><div class="stat-card-icon blue">🏆</div><div><div class="stat-card-num"><?= $wonBids ?></div><div class="stat-card-label">Winning Bids</div></div></div>
            </div>

            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">🔨 All Bids (<?= $bids->num_rows ?>)</div>
                </div>
                <table>
                    <thead>
                        <tr><th>#</th><th>Produce</th><th>Farmer</th><th>Trader</th><th>District</th><th>Base Price</th><th>Bid Amount</th><th>Status</th><th>Bid Time</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($bids->num_rows === 0): ?>
                        <tr><td colspan="9" style="text-align:center;padding:40px;color:#aaa;">No bids found.</td></tr>
                    <?php else: ?>
                    <?php $sr=1; while ($b = $bids->fetch_assoc()): ?>
                    <tr style="<?= $b['status']==='won'?'background:#f0fdf4;':'' ?>">
                        <td><?= $sr++ ?></td>
                        <td><strong><?= htmlspecialchars($b['produce_name']) ?></strong><br><small style="color:#aaa;"><?= $b['quantity'].' '.$b['unit'] ?></small></td>
                        <td><?= htmlspecialchars($b['farmer_name']) ?></td>
                        <td><strong><?= htmlspecialchars($b['trader_name']) ?></strong><br><small style="color:#aaa;"><?= $b['trader_phone'] ?></small></td>
                        <td><?= htmlspecialchars($b['trader_district']??'N/A') ?></td>
                        <td>₹<?= number_format($b['base_price'],2) ?></td>
                        <td style="font-size:18px;font-weight:700;color:var(--green-dark);">₹<?= number_format($b['bid_amount'],2) ?></td>
                        <td>
                            <?php if ($b['status']==='won'): ?>
                                <span class="badge badge-approved">🏆 Won</span>
                            <?php elseif ($b['status']==='lost'): ?>
                                <span class="badge badge-expired">Lost</span>
                            <?php else: ?>
                                <span class="badge badge-active">Active</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;color:#888;"><?= date('d M Y H:i', strtotime($b['created_at'])) ?></td>
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
