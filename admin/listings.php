<?php
require_once '../includes/config.php';
checkRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $action = $_POST['action'];
    if ($action === 'approve') {
        $conn->query("UPDATE listings SET status='active' WHERE id=$id");
    } elseif ($action === 'reject') {
        $conn->query("UPDATE listings SET status='expired' WHERE id=$id");
    } elseif ($action === 'close') {
        $topBid = $conn->query("SELECT b.*, l.farmer_id FROM bids b JOIN listings l ON b.listing_id=l.id WHERE b.listing_id=$id ORDER BY b.bid_amount DESC LIMIT 1")->fetch_assoc();
        if ($topBid) {
            $comm = $topBid['bid_amount'] * 0.02;
            $stmt = $conn->prepare("INSERT INTO transactions (listing_id, farmer_id, trader_id, final_amount, commission) VALUES (?,?,?,?,?)");
            $stmt->bind_param("iiidd", $id, $topBid['farmer_id'], $topBid['trader_id'], $topBid['bid_amount'], $comm);
            $stmt->execute();
            $conn->query("UPDATE listings SET status='sold' WHERE id=$id");
            $conn->query("UPDATE bids SET status='won' WHERE listing_id=$id AND bid_amount={$topBid['bid_amount']} LIMIT 1");
            $conn->query("UPDATE bids SET status='lost' WHERE listing_id=$id AND status='active'");
        }
    }
    header("Location: listings.php");
    exit();
}

$filter = $_GET['filter'] ?? 'all';
$where = "1=1";
if ($filter !== 'all') $where = "l.status='$filter'";

$listings = $conn->query("
    SELECT l.*, u.full_name as farmer_name, u.phone as farmer_phone,
    (SELECT COUNT(*) FROM bids b WHERE b.listing_id=l.id) as bid_count,
    (SELECT MAX(b.bid_amount) FROM bids b WHERE b.listing_id=l.id) as highest_bid
    FROM listings l JOIN users u ON l.farmer_id=u.id
    WHERE $where ORDER BY l.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Listings | APMC Admin</title>
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
            <a href="listings.php" class="active"><span class="sidebar-nav-icon">📦</span> All Listings</a>
            <a href="bids.php"><span class="sidebar-nav-icon">🔨</span> All Bids</a>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <div class="sidebar-section-label">Reports</div>
            <a href="reports.php"><span class="sidebar-nav-icon">📈</span> Reports</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>
    <div class="main-content">
        <div class="topbar"><div class="topbar-title">All Listings</div></div>
        <div class="page-content">
            <!-- Filter Tabs -->
            <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
                <?php foreach(['all'=>'All','pending'=>'⏳ Pending','active'=>'🔴 Active','sold'=>'✅ Sold','expired'=>'❌ Expired'] as $key=>$label): ?>
                <a href="listings.php?filter=<?= $key ?>" class="btn-sm <?= $filter===$key?'btn-sm-green':'btn-sm-blue' ?>" style="padding:10px 20px;"><?= $label ?></a>
                <?php endforeach; ?>
            </div>

            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">📦 Listings (<?= $listings->num_rows ?>)</div>
                </div>
                <table>
                    <thead>
                        <tr><th>#</th><th>Produce</th><th>Farmer</th><th>Qty</th><th>Base Price</th><th>Highest Bid</th><th>Bids</th><th>Status</th><th>Bid Ends</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($listings->num_rows === 0): ?>
                        <tr><td colspan="10" style="text-align:center;padding:40px;color:#aaa;">No listings found.</td></tr>
                    <?php else: ?>
                    <?php $sr=1; while ($l = $listings->fetch_assoc()): ?>
                    <tr>
                        <td><?= $sr++ ?></td>
                        <td><strong><?= htmlspecialchars($l['produce_name']) ?></strong><br><small style="color:#aaa;"><?= $l['category'] ?></small></td>
                        <td><?= htmlspecialchars($l['farmer_name']) ?><br><small style="color:#aaa;"><?= $l['farmer_phone'] ?></small></td>
                        <td><?= $l['quantity'] ?> <?= $l['unit'] ?></td>
                        <td>₹<?= number_format($l['base_price'],2) ?></td>
                        <td style="font-weight:700;color:var(--green-dark);"><?= $l['highest_bid'] ? '₹'.number_format($l['highest_bid'],2) : '—' ?></td>
                        <td><span class="badge badge-active"><?= $l['bid_count'] ?></span></td>
                        <td><span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span></td>
                        <td style="font-size:13px;"><?= date('d M Y H:i', strtotime($l['bid_end_time'])) ?></td>
                        <td class="action-btns">
                            <?php if ($l['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $l['id'] ?>"><input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-sm btn-sm-green" onclick="return confirm('Approve listing?')">✅ Approve</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $l['id'] ?>"><input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-sm btn-sm-red" onclick="return confirm('Reject listing?')">❌ Reject</button>
                                </form>
                            <?php elseif ($l['status'] === 'active' && $l['bid_count'] > 0): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $l['id'] ?>"><input type="hidden" name="action" value="close">
                                    <button type="submit" class="btn-sm btn-sm-amber" onclick="return confirm('Close bidding and award to highest bidder?')">🏆 Close & Award</button>
                                </form>
                            <?php else: ?>
                                <span style="font-size:12px;color:#aaa;">No action</span>
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
