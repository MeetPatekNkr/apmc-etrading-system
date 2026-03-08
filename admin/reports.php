<?php
require_once '../includes/config.php';
checkRole('admin');

// Summary data
$totalFarmers   = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='farmer' AND is_approved=1")->fetch_assoc()['c'];
$totalTraders   = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='trader' AND is_approved=1")->fetch_assoc()['c'];
$totalListings  = $conn->query("SELECT COUNT(*) as c FROM listings")->fetch_assoc()['c'];
$soldListings   = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='sold'")->fetch_assoc()['c'];
$activeListings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='active'")->fetch_assoc()['c'];
$totalBids      = $conn->query("SELECT COUNT(*) as c FROM bids")->fetch_assoc()['c'];
$totalTxn       = $conn->query("SELECT COUNT(*) as c FROM transactions")->fetch_assoc()['c'];
$totalValue     = $conn->query("SELECT COALESCE(SUM(final_amount),0) as r FROM transactions")->fetch_assoc()['r'];
$totalComm      = $conn->query("SELECT COALESCE(SUM(commission),0) as r FROM transactions")->fetch_assoc()['r'];

// Category wise sales
$catSales = $conn->query("
    SELECT l.category, COUNT(*) as count, COALESCE(SUM(t.final_amount),0) as total
    FROM listings l
    LEFT JOIN transactions t ON l.id = t.listing_id
    WHERE l.status='sold'
    GROUP BY l.category ORDER BY total DESC
");

// Top traders
$topTraders = $conn->query("
    SELECT u.full_name, u.district, COUNT(*) as wins, COALESCE(SUM(t.final_amount),0) as total_spent
    FROM transactions t JOIN users u ON t.trader_id = u.id
    GROUP BY t.trader_id ORDER BY total_spent DESC LIMIT 5
");

// Top farmers
$topFarmers = $conn->query("
    SELECT u.full_name, u.village, COUNT(*) as sales, COALESCE(SUM(t.final_amount),0) as total_earned
    FROM transactions t JOIN users u ON t.farmer_id = u.id
    GROUP BY t.farmer_id ORDER BY total_earned DESC LIMIT 5
");

// Monthly transactions
$monthlyData = $conn->query("
    SELECT DATE_FORMAT(transaction_date,'%b %Y') as month,
           COUNT(*) as count, SUM(final_amount) as total
    FROM transactions
    GROUP BY DATE_FORMAT(transaction_date,'%Y-%m')
    ORDER BY transaction_date DESC LIMIT 6
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports | APMC Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .report-section { background:white; border-radius:16px; padding:28px; box-shadow:0 2px 12px rgba(0,0,0,0.06); margin-bottom:24px; }
        .report-section h3 { font-family:var(--font-display); font-size:20px; color:var(--green-dark); margin-bottom:20px; padding-bottom:12px; border-bottom:2px solid var(--cream-dark); }
        .progress-bar { height:10px; background:var(--cream-dark); border-radius:999px; margin-top:6px; overflow:hidden; }
        .progress-fill { height:100%; background:linear-gradient(90deg, var(--green-light), var(--green-accent)); border-radius:999px; transition:width 1s ease; }
        .summary-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
        .summary-item { background:var(--cream); border-radius:12px; padding:20px; text-align:center; }
        .summary-num { font-family:var(--font-display); font-size:28px; font-weight:700; color:var(--green-dark); }
        .summary-label { font-size:13px; color:#888; margin-top:4px; }
    </style>
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
            <a href="bids.php"><span class="sidebar-nav-icon">🔨</span> All Bids</a>
            <a href="transactions.php"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <div class="sidebar-section-label">Reports</div>
            <a href="reports.php" class="active"><span class="sidebar-nav-icon">📈</span> Reports</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">Reports & Analytics</div>
            <div class="topbar-right"><button onclick="window.print()" class="btn-add" style="background:#555;">🖨️ Print Report</button></div>
        </div>
        <div class="page-content">

            <!-- Overview Summary -->
            <div class="report-section">
                <h3>📊 Platform Overview</h3>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
                    <div class="summary-item"><div class="summary-num"><?= $totalFarmers ?></div><div class="summary-label">Approved Farmers</div></div>
                    <div class="summary-item"><div class="summary-num"><?= $totalTraders ?></div><div class="summary-label">Approved Traders</div></div>
                    <div class="summary-item"><div class="summary-num"><?= $totalListings ?></div><div class="summary-label">Total Listings</div></div>
                    <div class="summary-item"><div class="summary-num"><?= $totalBids ?></div><div class="summary-label">Total Bids Placed</div></div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:16px;">
                    <div class="summary-item" style="background:#f0fdf4;">
                        <div class="summary-num" style="color:#16a34a;"><?= $soldListings ?></div>
                        <div class="summary-label">Listings Sold</div>
                    </div>
                    <div class="summary-item" style="background:#fefce8;">
                        <div class="summary-num" style="color:#ca8a04;">₹<?= number_format($totalValue,0) ?></div>
                        <div class="summary-label">Total Trade Value</div>
                    </div>
                    <div class="summary-item" style="background:#eff6ff;">
                        <div class="summary-num" style="color:#2563eb;">₹<?= number_format($totalComm,0) ?></div>
                        <div class="summary-label">APMC Commission Earned</div>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

                <!-- Category-wise Sales -->
                <div class="report-section">
                    <h3>🌾 Category-wise Sales</h3>
                    <?php if ($catSales->num_rows === 0): ?>
                        <p style="color:#aaa;text-align:center;padding:20px;">No sales data yet.</p>
                    <?php else: ?>
                        <?php
                        $catData = [];
                        $maxVal = 1;
                        while ($row = $catSales->fetch_assoc()) { $catData[] = $row; if ($row['total'] > $maxVal) $maxVal = $row['total']; }
                        $icons = ['Grains & Cereals'=>'🌾','Cotton'=>'🌿','Vegetables'=>'🥦','Fruits'=>'🍎','Pulses'=>'🫘','Oilseeds'=>'🌻','Spices'=>'🌶️','Sugarcane'=>'🎋','Other'=>'🌱'];
                        foreach ($catData as $row):
                            $pct = $maxVal > 0 ? round(($row['total']/$maxVal)*100) : 0;
                        ?>
                        <div style="margin-bottom:16px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-weight:600;font-size:14px;"><?= ($icons[$row['category']]??'🌱').' '.htmlspecialchars($row['category']) ?></span>
                                <span style="font-size:13px;color:#888;"><?= $row['count'] ?> sales • ₹<?= number_format($row['total'],0) ?></span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Monthly Transactions -->
                <div class="report-section">
                    <h3>📅 Monthly Transactions</h3>
                    <?php if ($monthlyData->num_rows === 0): ?>
                        <p style="color:#aaa;text-align:center;padding:20px;">No data yet.</p>
                    <?php else: ?>
                    <table style="width:100%;font-size:14px;">
                        <thead><tr><th style="text-align:left;padding:8px 0;color:#888;font-size:12px;text-transform:uppercase;">Month</th><th style="text-align:center;color:#888;font-size:12px;text-transform:uppercase;">Txns</th><th style="text-align:right;color:#888;font-size:12px;text-transform:uppercase;">Value</th></tr></thead>
                        <tbody>
                        <?php while ($m = $monthlyData->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid var(--cream-dark);">
                            <td style="padding:10px 0;font-weight:600;"><?= $m['month'] ?></td>
                            <td style="text-align:center;"><span class="badge badge-active"><?= $m['count'] ?></span></td>
                            <td style="text-align:right;font-weight:700;color:var(--green-dark);">₹<?= number_format($m['total'],0) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <!-- Top Traders -->
                <div class="report-section">
                    <h3>🏆 Top Traders</h3>
                    <?php if ($topTraders->num_rows === 0): ?>
                        <p style="color:#aaa;text-align:center;padding:20px;">No data yet.</p>
                    <?php else: ?>
                    <?php $rank=1; while ($t = $topTraders->fetch_assoc()): ?>
                    <div style="display:flex;align-items:center;gap:16px;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                        <div style="width:36px;height:36px;background:var(--amber);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--green-dark);flex-shrink:0;"><?= $rank++ ?></div>
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:15px;"><?= htmlspecialchars($t['full_name']) ?></div>
                            <div style="font-size:12px;color:#888;"><?= htmlspecialchars($t['district']??'N/A') ?> • <?= $t['wins'] ?> purchases</div>
                        </div>
                        <div style="font-weight:700;color:var(--green-dark);">₹<?= number_format($t['total_spent'],0) ?></div>
                    </div>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </div>

                <!-- Top Farmers -->
                <div class="report-section">
                    <h3>👨‍🌾 Top Earning Farmers</h3>
                    <?php if ($topFarmers->num_rows === 0): ?>
                        <p style="color:#aaa;text-align:center;padding:20px;">No data yet.</p>
                    <?php else: ?>
                    <?php $rank=1; while ($f = $topFarmers->fetch_assoc()): ?>
                    <div style="display:flex;align-items:center;gap:16px;padding:12px 0;border-bottom:1px solid var(--cream-dark);">
                        <div style="width:36px;height:36px;background:var(--green-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:white;flex-shrink:0;"><?= $rank++ ?></div>
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:15px;"><?= htmlspecialchars($f['full_name']) ?></div>
                            <div style="font-size:12px;color:#888;"><?= htmlspecialchars($f['village']??'N/A') ?> • <?= $f['sales'] ?> sales</div>
                        </div>
                        <div style="font-weight:700;color:var(--green-dark);">₹<?= number_format($f['total_earned'],0) ?></div>
                    </div>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>
