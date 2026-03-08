<?php
require_once '../includes/config.php';
checkRole('trader');
$traderId = $_SESSION['user_id'];

// Filter
$category = sanitize($conn, $_GET['category'] ?? '');
$search = sanitize($conn, $_GET['search'] ?? '');
$where = "l.status = 'active' AND l.bid_end_time > NOW()";
if ($category) $where .= " AND l.category = '$category'";
if ($search) $where .= " AND l.produce_name LIKE '%$search%'";

$listings = $conn->query("
    SELECT l.*, u.full_name AS farmer_name,
    (SELECT MAX(b.bid_amount) FROM bids b WHERE b.listing_id = l.id) AS highest_bid,
    (SELECT COUNT(*) FROM bids b WHERE b.listing_id = l.id) AS bid_count,
    (SELECT b.bid_amount FROM bids b WHERE b.listing_id = l.id AND b.trader_id = $traderId ORDER BY b.created_at DESC LIMIT 1) AS my_bid
    FROM listings l JOIN users u ON l.farmer_id = u.id
    WHERE $where ORDER BY l.created_at DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_bid'])) {
    $listing_id = intval($_POST['listing_id']);
    $bid_amount = floatval($_POST['bid_amount']);
    $maxBid = $conn->query("SELECT COALESCE(MAX(bid_amount),0) as m FROM bids WHERE listing_id=$listing_id")->fetch_assoc()['m'];
    $basePrice = $conn->query("SELECT base_price FROM listings WHERE id=$listing_id AND status='active'")->fetch_assoc()['base_price'] ?? 0;
    if ($bid_amount > $maxBid && $bid_amount > $basePrice) {
        $stmt = $conn->prepare("INSERT INTO bids (listing_id, trader_id, bid_amount) VALUES (?,?,?)");
        $stmt->bind_param("iid", $listing_id, $traderId, $bid_amount);
        $stmt->execute();
    }
    header("Location: browse-listings.php?msg=ok&category=$category&search=$search");
    exit();
}

$icons = ['Grains & Cereals'=>'🌾','Cotton'=>'🌿','Vegetables'=>'🥦','Fruits'=>'🍎','Pulses'=>'🫘','Oilseeds'=>'🌻','Spices'=>'🌶️','Sugarcane'=>'🎋','Other'=>'🌱'];
$categories = array_keys($icons);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Listings | APMC e-Trading</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="sidebar-brand-name">🌾 APMC</div><div class="sidebar-brand-sub">e-Trading System</div></div>
        <div class="sidebar-user"><div class="sidebar-avatar">🤝</div><div><div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div><div class="sidebar-user-role">Trader</div></div></div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><span class="sidebar-nav-icon">📊</span> Dashboard</a>
            <a href="browse-listings.php" class="active"><span class="sidebar-nav-icon">🌾</span> Browse Listings</a>
            <a href="my-bids.php"><span class="sidebar-nav-icon">🔨</span> My Bids</a>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php"><span class="sidebar-nav-icon">👤</span> Profile</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">Browse Live Auctions</div>
        </div>
        <div class="page-content">
            <?php if (isset($_GET['msg'])): ?><div class="alert alert-success">✅ Bid placed successfully!</div><?php endif; ?>

            <!-- Filters -->
            <form method="GET" style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
                <input type="text" name="search" placeholder="🔍 Search produce..." value="<?= htmlspecialchars($search) ?>" style="padding:10px 16px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;outline:none;min-width:200px;">
                <select name="category" style="padding:10px 16px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;outline:none;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $icons[$cat] ?> <?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-add">Filter</button>
                <?php if ($category || $search): ?><a href="browse-listings.php" class="btn-sm btn-sm-red" style="padding:10px 16px;">Clear</a><?php endif; ?>
            </form>

            <?php if ($listings->num_rows === 0): ?>
                <div class="alert alert-info">No active listings found. <?= ($category || $search) ? '<a href="browse-listings.php">Clear filters</a>' : 'Check back later.' ?></div>
            <?php else: ?>
            <div class="listing-grid">
                <?php while ($l = $listings->fetch_assoc()):
                    $icon = $icons[$l['category']] ?? '🌾';
                    $highestBid = $l['highest_bid'] ?: $l['base_price'];
                ?>
                <div class="listing-card">
                    <div class="listing-card-img"><?= $icon ?></div>
                    <div class="listing-card-body">
                        <div class="listing-card-title"><?= htmlspecialchars($l['produce_name']) ?></div>
                        <div class="listing-card-meta">
                            <?= $l['quantity'] ?> <?= $l['unit'] ?> • <?= $l['category'] ?><br>
                            👨‍🌾 <?= htmlspecialchars($l['farmer_name']) ?>
                            <?= $l['location'] ? '• 📍 ' . htmlspecialchars($l['location']) : '' ?>
                        </div>
                        <div style="display:flex;gap:16px;">
                            <div>
                                <div style="font-size:11px;color:#aaa;text-transform:uppercase;">Base</div>
                                <div style="font-weight:600;">₹<?= number_format($l['base_price'],0) ?></div>
                            </div>
                            <div>
                                <div style="font-size:11px;color:#aaa;text-transform:uppercase;">Highest Bid</div>
                                <div class="listing-card-price">₹<?= number_format($highestBid,0) ?> <span>/<?= $l['unit'] ?></span></div>
                            </div>
                        </div>
                        <?php if ($l['my_bid']): ?>
                            <div style="font-size:12px;color:#d97706;background:#fef3c7;padding:4px 10px;border-radius:6px;display:inline-block;margin-top:8px;">
                                Your bid: ₹<?= number_format($l['my_bid'],0) ?> <?= $l['my_bid'] >= $highestBid ? '🏆 Leading!' : '— Outbid' ?>
                            </div>
                        <?php endif; ?>
                        <div class="listing-card-footer">
                            <div class="countdown" id="countdown-<?= $l['id'] ?>" data-end="<?= $l['bid_end_time'] ?>">⏱ Loading...</div>
                            <span style="font-size:13px;color:#888;"><?= $l['bid_count'] ?> bids</span>
                        </div>
                        <?php if ($l['description']): ?>
                            <div style="font-size:12px;color:#888;margin-top:8px;line-height:1.5;"><?= htmlspecialchars(substr($l['description'],0,100)) ?>...</div>
                        <?php endif; ?>
                        <form method="POST" class="bid-input-wrap">
                            <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                            <input type="hidden" name="place_bid" value="1">
                            <input type="number" name="bid_amount" placeholder="₹ Enter Bid" step="1" min="<?= $highestBid + 1 ?>">
                            <button type="submit" class="bid-btn">Bid Now</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="../js/countdown.js"></script>
</body>
</html>
