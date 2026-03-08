<?php
require_once '../includes/config.php';
checkRole('admin');

$adminName = $_SESSION['user_name'];

// Handle actions
if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    if ($action === 'approve_user') {
        $conn->query("UPDATE users SET is_approved = 1 WHERE id = $id");
    } elseif ($action === 'reject_user') {
        $conn->query("DELETE FROM users WHERE id = $id AND role != 'admin'");
    } elseif ($action === 'approve_listing') {
        $conn->query("UPDATE listings SET status = 'active' WHERE id = $id");
    } elseif ($action === 'reject_listing') {
        $conn->query("UPDATE listings SET status = 'expired' WHERE id = $id");
    } elseif ($action === 'close_bid') {
        // Find highest bid
        $result = $conn->query("SELECT b.*, l.farmer_id, l.quantity, l.base_price FROM bids b JOIN listings l ON b.listing_id = l.id WHERE b.listing_id = $id ORDER BY b.bid_amount DESC LIMIT 1");
        $topBid = $result->fetch_assoc();
        if ($topBid) {
            $listingId = $id;
            $commission = $topBid['bid_amount'] * 0.02;
            $stmt = $conn->prepare("INSERT INTO transactions (listing_id, farmer_id, trader_id, final_amount, commission) VALUES (?,?,?,?,?)");
            $stmt->bind_param("iiidd", $listingId, $topBid['farmer_id'], $topBid['trader_id'], $topBid['bid_amount'], $commission);
            $stmt->execute();
            $conn->query("UPDATE listings SET status = 'sold' WHERE id = $id");
            $conn->query("UPDATE bids SET status = 'won' WHERE listing_id = $id AND bid_amount = {$topBid['bid_amount']} LIMIT 1");
            $conn->query("UPDATE bids SET status = 'lost' WHERE listing_id = $id AND status = 'active'");
        }
    }
    header("Location: dashboard.php");
    exit();
}

// Stats
$totalFarmers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='farmer'")->fetch_assoc()['c'];
$totalTraders = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='trader'")->fetch_assoc()['c'];
$pendingUsers = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_approved=0 AND role!='admin'")->fetch_assoc()['c'];
$activeListings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='active'")->fetch_assoc()['c'];
$pendingListings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='pending'")->fetch_assoc()['c'];
$totalTransactions = $conn->query("SELECT COUNT(*) as c FROM transactions")->fetch_assoc()['c'];
$totalRevenue = $conn->query("SELECT COALESCE(SUM(final_amount),0) as r FROM transactions")->fetch_assoc()['r'];
$totalCommission = $conn->query("SELECT COALESCE(SUM(commission),0) as r FROM transactions")->fetch_assoc()['r'];

// Pending users
$pendingUsersData = $conn->query("SELECT * FROM users WHERE is_approved=0 AND role!='admin' ORDER BY created_at DESC");

// Pending listings
$pendingListingsData = $conn->query("SELECT l.*, u.full_name as farmer_name FROM listings l JOIN users u ON l.farmer_id = u.id WHERE l.status='pending' ORDER BY l.created_at DESC");

// Active listings
$activeListingsData = $conn->query("SELECT l.*, u.full_name as farmer_name, (SELECT COUNT(*) FROM bids b WHERE b.listing_id=l.id) as bid_count FROM listings l JOIN users u ON l.farmer_id = u.id WHERE l.status='active' ORDER BY l.created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | APMC e-Trading</title>
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
            <div class="sidebar-avatar">🏛️</div>
            <div>
                <div class="sidebar-user-name"><?= htmlspecialchars($adminName) ?></div>
                <div class="sidebar-user-role">APMC Officer</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Administration</div>
            <a href="dashboard.php" class="active"><span class="sidebar-nav-icon">📊</span> Dashboard</a>
            <a href="users.php"><span class="sidebar-nav-icon">👥</span> All Users</a>
            <a href="listings.php"><span class="sidebar-nav-icon">📦</span> All Listings</a>
            <a href="bids.php"><span class="sidebar-nav-icon">🔨</span> All Bids</a>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <div class="sidebar-section-label">Reports</div>
            <a href="reports.php"><span class="sidebar-nav-icon">📈</span> Reports</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">APMC Officer Dashboard</div>
            <div class="topbar-right">
                <?php if ($pendingUsers > 0 || $pendingListings > 0): ?>
                    <button class="notif-btn">🔔 <span class="notif-badge"><?= $pendingUsers + $pendingListings ?></span></button>
                <?php endif; ?>
                <span style="font-size:14px;color:#666;">Namaste, <?= htmlspecialchars(explode(' ',$adminName)[0]) ?> 🙏</span>
            </div>
        </div>

        <div class="page-content">
            <!-- Stats -->
            <div class="stats-row" style="grid-template-columns:repeat(4,1fr);">
                <div class="stat-card">
                    <div class="stat-card-icon green">👨‍🌾</div>
                    <div>
                        <div class="stat-card-num"><?= $totalFarmers ?></div>
                        <div class="stat-card-label">Farmers</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon amber">🤝</div>
                    <div>
                        <div class="stat-card-num"><?= $totalTraders ?></div>
                        <div class="stat-card-label">Traders</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon blue">📦</div>
                    <div>
                        <div class="stat-card-num"><?= $activeListings ?></div>
                        <div class="stat-card-label">Active Auctions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon green">💰</div>
                    <div>
                        <div class="stat-card-num">₹<?= number_format($totalRevenue,0) ?></div>
                        <div class="stat-card-label">Total Traded</div>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <div class="stat-card">
                    <div class="stat-card-icon amber">⏳</div>
                    <div>
                        <div class="stat-card-num"><?= $pendingUsers ?></div>
                        <div class="stat-card-label">Pending User Approvals</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon amber">📋</div>
                    <div>
                        <div class="stat-card-num"><?= $pendingListings ?></div>
                        <div class="stat-card-label">Pending Listing Approvals</div>
                    </div>
                </div>
            </div>

            <!-- Pending User Approvals -->
            <?php if ($pendingUsersData->num_rows > 0): ?>
            <div class="data-table-wrap" style="margin-bottom:24px;">
                <div class="data-table-header">
                    <div class="data-table-title">⏳ Pending User Approvals</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th><th>Role</th><th>Email</th><th>Phone</th><th>District</th><th>Registered</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($u = $pendingUsersData->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                        <td><span class="badge badge-pending"><?= ucfirst($u['role']) ?></span></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td><?= htmlspecialchars($u['district'] ?: 'N/A') ?></td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td class="action-btns">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="approve_user">
                                <button type="submit" class="btn-sm btn-sm-green" onclick="return confirm('Approve this user?')">✅ Approve</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="reject_user">
                                <button type="submit" class="btn-sm btn-sm-red" onclick="return confirm('Reject and delete this user?')">❌ Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Pending Listing Approvals -->
            <?php if ($pendingListingsData->num_rows > 0): ?>
            <div class="data-table-wrap" style="margin-bottom:24px;">
                <div class="data-table-header">
                    <div class="data-table-title">📋 Pending Listing Approvals</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Produce</th><th>Farmer</th><th>Qty</th><th>Base Price</th><th>Bid Ends</th><th>Location</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($l = $pendingListingsData->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($l['produce_name']) ?></strong><br><small><?= $l['category'] ?></small></td>
                        <td><?= htmlspecialchars($l['farmer_name']) ?></td>
                        <td><?= $l['quantity'] ?> <?= $l['unit'] ?></td>
                        <td>₹<?= number_format($l['base_price'],2) ?></td>
                        <td><?= date('d M Y H:i', strtotime($l['bid_end_time'])) ?></td>
                        <td><?= htmlspecialchars($l['location'] ?: 'N/A') ?></td>
                        <td class="action-btns">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <input type="hidden" name="action" value="approve_listing">
                                <button type="submit" class="btn-sm btn-sm-green" onclick="return confirm('Approve this listing for bidding?')">✅ Approve</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <input type="hidden" name="action" value="reject_listing">
                                <button type="submit" class="btn-sm btn-sm-red" onclick="return confirm('Reject this listing?')">❌ Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Active Listings -->
            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">🔴 Active Auctions</div>
                    <a href="listings.php" class="btn-add">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Produce</th><th>Farmer</th><th>Qty</th><th>Base Price</th><th>Bids</th><th>Ends At</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($activeListingsData->num_rows === 0): ?>
                        <tr><td colspan="7" style="text-align:center;color:#aaa;padding:40px;">No active auctions</td></tr>
                    <?php else: ?>
                    <?php while ($l = $activeListingsData->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($l['produce_name']) ?></strong></td>
                        <td><?= htmlspecialchars($l['farmer_name']) ?></td>
                        <td><?= $l['quantity'] ?> <?= $l['unit'] ?></td>
                        <td>₹<?= number_format($l['base_price'],2) ?></td>
                        <td><span class="badge badge-active"><?= $l['bid_count'] ?></span></td>
                        <td><?= date('d M H:i', strtotime($l['bid_end_time'])) ?></td>
                        <td>
                            <?php if ($l['bid_count'] > 0): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <input type="hidden" name="action" value="close_bid">
                                <button type="submit" class="btn-sm btn-sm-amber" onclick="return confirm('Close bidding and award to highest bidder?')">🏆 Close & Award</button>
                            </form>
                            <?php else: ?>
                            <span style="font-size:12px;color:#aaa;">No bids yet</span>
                            <?php endif; ?>
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
