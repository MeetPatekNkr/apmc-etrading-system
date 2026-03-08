<?php
require_once '../includes/config.php';
checkRole('trader');

$traderId = $_SESSION['user_id'];
$traderName = $_SESSION['user_name'];

// Stats
$totalBids = $conn->query("SELECT COUNT(*) as c FROM bids WHERE trader_id = $traderId")->fetch_assoc()['c'];
$wonBids = $conn->query("SELECT COUNT(*) as c FROM bids WHERE trader_id = $traderId AND status = 'won'")->fetch_assoc()['c'];
$activeListings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status = 'active'")->fetch_assoc()['c'];
$totalSpent = $conn->query("SELECT COALESCE(SUM(final_amount),0) as r FROM transactions WHERE trader_id = $traderId")->fetch_assoc()['r'];

// Active listings available for bidding
$listings = $conn->query("
    SELECT l.*, u.full_name AS farmer_name,
    (SELECT MAX(b.bid_amount) FROM bids b WHERE b.listing_id = l.id) AS highest_bid,
    (SELECT COUNT(*) FROM bids b WHERE b.listing_id = l.id) AS bid_count,
    (SELECT b.bid_amount FROM bids b WHERE b.listing_id = l.id AND b.trader_id = $traderId ORDER BY b.created_at DESC LIMIT 1) AS my_bid
    FROM listings l
    JOIN users u ON l.farmer_id = u.id
    WHERE l.status = 'active' AND l.bid_end_time > NOW()
    ORDER BY l.created_at DESC
    LIMIT 12
");

// Handle bid submission
$bidMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_bid'])) {
    $listing_id = intval($_POST['listing_id']);
    $bid_amount = floatval($_POST['bid_amount']);

    // Validate
    $lCheck = $conn->query("SELECT base_price, status FROM listings WHERE id = $listing_id AND status='active'")->fetch_assoc();
    $maxBid = $conn->query("SELECT COALESCE(MAX(bid_amount),0) as m FROM bids WHERE listing_id = $listing_id")->fetch_assoc()['m'];

    if (!$lCheck) {
        $bidMsg = '<div class="alert alert-error">Listing not available for bidding.</div>';
    } elseif ($bid_amount <= $lCheck['base_price']) {
        $bidMsg = '<div class="alert alert-error">Bid must be higher than base price ₹' . number_format($lCheck['base_price'],2) . '</div>';
    } elseif ($bid_amount <= $maxBid) {
        $bidMsg = '<div class="alert alert-error">Bid must be higher than current highest bid ₹' . number_format($maxBid,2) . '</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO bids (listing_id, trader_id, bid_amount) VALUES (?,?,?)");
        $stmt->bind_param("iid", $listing_id, $traderId, $bid_amount);
        if ($stmt->execute()) {
            $bidMsg = '<div class="alert alert-success">✅ Bid placed successfully! ₹' . number_format($bid_amount,2) . '</div>';
        } else {
            $bidMsg = '<div class="alert alert-error">Failed to place bid.</div>';
        }
    }
    // Refresh listings
    header("Location: dashboard.php?msg=bid_placed");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trader Dashboard | APMC e-Trading</title>
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
                <div class="sidebar-user-name"><?= htmlspecialchars($traderName) ?></div>
                <div class="sidebar-user-role">Trader</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="dashboard.php" class="active"><span class="sidebar-nav-icon">📊</span> Dashboard</a>
            <a href="browse-listings.php"><span class="sidebar-nav-icon">🌾</span> Browse Listings</a>
            <a href="my-bids.php"><span class="sidebar-nav-icon">🔨</span> My Bids</a>
            <div class="sidebar-section-label">Account</div>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php"><span class="sidebar-nav-icon">👤</span> Profile</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">Trader Dashboard</div>
            <div class="topbar-right">
                <button class="notif-btn">🔔</button>
                <span style="font-size:14px;color:#666;">Welcome, <?= htmlspecialchars(explode(' ',$traderName)[0]) ?> 👋</span>
            </div>
        </div>

        <div class="page-content">
            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'bid_placed'): ?>
                <div class="alert alert-success">✅ Bid placed successfully!</div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-card-icon green">🌾</div>
                    <div>
                        <div class="stat-card-num"><?= $activeListings ?></div>
                        <div class="stat-card-label">Live Auctions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon amber">🔨</div>
                    <div>
                        <div class="stat-card-num"><?= $totalBids ?></div>
                        <div class="stat-card-label">Total Bids Placed</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon blue">🏆</div>
                    <div>
                        <div class="stat-card-num"><?= $wonBids ?></div>
                        <div class="stat-card-label">Bids Won</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon green">💰</div>
                    <div>
                        <div class="stat-card-num">₹<?= number_format($totalSpent,0) ?></div>
                        <div class="stat-card-label">Total Purchased</div>
                    </div>
                </div>
            </div>

            <!-- Live Auctions -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h2 style="font-family:var(--font-display);color:var(--green-dark);font-size:22px;">🔴 Live Auctions</h2>
                <a href="browse-listings.php" class="btn-add">View All Listings</a>
            </div>

            <?php if ($listings->num_rows === 0): ?>
                <div class="alert alert-info">No active auctions at the moment. Check back soon!</div>
            <?php else: ?>
            <div class="listing-grid">
                <?php while ($l = $listings->fetch_assoc()):
                    $icons = ['Grains & Cereals'=>'🌾','Cotton'=>'🌿','Vegetables'=>'🥦','Fruits'=>'🍎','Pulses'=>'🫘','Oilseeds'=>'🌻','Spices'=>'🌶️','Sugarcane'=>'🎋','Other'=>'🌱'];
                    $icon = $icons[$l['category']] ?? '🌾';
                    $highestBid = $l['highest_bid'] ?: $l['base_price'];
                    $myBid = $l['my_bid'];
                ?>
                <div class="listing-card">
                    <div class="listing-card-img"><?= $icon ?></div>
                    <div class="listing-card-body">
                        <div class="listing-card-title"><?= htmlspecialchars($l['produce_name']) ?></div>
                        <div class="listing-card-meta">
                            <?= $l['quantity'] ?> <?= $l['unit'] ?> • <?= htmlspecialchars($l['category']) ?><br>
                            👨‍🌾 <?= htmlspecialchars($l['farmer_name']) ?> • 📍 <?= htmlspecialchars($l['location'] ?: 'N/A') ?>
                        </div>

                        <div style="display:flex;gap:16px;margin-bottom:8px;">
                            <div>
                                <div style="font-size:11px;color:#aaa;text-transform:uppercase;letter-spacing:1px;">Base Price</div>
                                <div style="font-weight:600;color:#666;">₹<?= number_format($l['base_price'],0) ?></div>
                            </div>
                            <div>
                                <div style="font-size:11px;color:#aaa;text-transform:uppercase;letter-spacing:1px;">Highest Bid</div>
                                <div class="listing-card-price">₹<?= number_format($highestBid,0) ?> <span>/<?= $l['unit'] ?></span></div>
                            </div>
                        </div>

                        <?php if ($myBid): ?>
                            <div style="font-size:12px;color:#d97706;background:#fef3c7;padding:4px 10px;border-radius:6px;display:inline-block;margin-bottom:8px;">
                                Your bid: ₹<?= number_format($myBid,0) ?>
                                <?= $myBid >= $highestBid ? ' 🏆 Leading!' : ' — Outbid' ?>
                            </div>
                        <?php endif; ?>

                        <div class="listing-card-footer">
                            <div class="countdown" id="countdown-<?= $l['id'] ?>" data-end="<?= $l['bid_end_time'] ?>">⏱ Loading...</div>
                            <span style="font-size:13px;color:#888;"><?= $l['bid_count'] ?> bids</span>
                        </div>

                        <form method="POST" class="bid-input-wrap">
                            <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                            <input type="hidden" name="place_bid" value="1">
                            <input type="number" name="bid_amount" placeholder="₹ Your Bid" step="1" min="<?= $highestBid + 1 ?>">
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
