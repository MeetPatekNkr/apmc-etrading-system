<?php
require_once '../includes/config.php';
checkRole('farmer');
$farmerId = $_SESSION['user_id'];

// All completed transactions
$transactions = $conn->query("
    SELECT t.*, l.produce_name, l.quantity, l.unit, l.category,
           u.full_name as trader_name, u.phone as trader_phone, u.district as trader_district
    FROM transactions t
    JOIN listings l ON t.listing_id = l.id
    JOIN users u ON t.trader_id = u.id
    WHERE t.farmer_id = $farmerId
    ORDER BY t.transaction_date DESC
");

// Summary stats
$totalEarned  = $conn->query("SELECT COALESCE(SUM(final_amount),0) as r FROM transactions WHERE farmer_id=$farmerId")->fetch_assoc()['r'];
$totalSales   = $conn->query("SELECT COUNT(*) as c FROM transactions WHERE farmer_id=$farmerId")->fetch_assoc()['c'];
$totalComm    = $conn->query("SELECT COALESCE(SUM(commission),0) as r FROM transactions WHERE farmer_id=$farmerId")->fetch_assoc()['r'];
$netAmount    = $totalEarned - $totalComm;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions | APMC e-Trading</title>
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
            <a href="my-bids.php"><span class="sidebar-nav-icon">🔨</span> Bids Received</a>
            <div class="sidebar-section-label">Account</div>
            <a href="transactions.php" class="active"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php"><span class="sidebar-nav-icon">👤</span> My Profile</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">My Transactions</div>
        </div>

        <div class="page-content">

            <!-- Summary Cards -->
            <div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:28px;">
                <div class="stat-card">
                    <div class="stat-card-icon green">✅</div>
                    <div>
                        <div class="stat-card-num"><?= $totalSales ?></div>
                        <div class="stat-card-label">Completed Sales</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon amber">💰</div>
                    <div>
                        <div class="stat-card-num">₹<?= number_format($totalEarned, 0) ?></div>
                        <div class="stat-card-label">Total Sale Value</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon red">🏛️</div>
                    <div>
                        <div class="stat-card-num">₹<?= number_format($totalComm, 0) ?></div>
                        <div class="stat-card-label">APMC Commission (2%)</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon blue">🏦</div>
                    <div>
                        <div class="stat-card-num">₹<?= number_format($netAmount, 0) ?></div>
                        <div class="stat-card-label">Net Amount Receivable</div>
                    </div>
                </div>
            </div>

            <?php if ($transactions->num_rows === 0): ?>
                <div class="alert alert-info">
                    💰 Abhi koi transaction nahi hai. Jab aapki listing ki bidding close hogi aur sale complete hogi, yahan record aayega.
                </div>
            <?php else: ?>

            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">💰 Transaction History</div>
                    <button onclick="window.print()" class="btn-add" style="background:#555;">🖨️ Print</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Txn ID</th>
                            <th>Produce</th>
                            <th>Qty</th>
                            <th>Buyer (Trader)</th>
                            <th>Sale Amount</th>
                            <th>Commission (2%)</th>
                            <th>Net Amount</th>
                            <th>Payment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($t = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td style="font-size:12px;color:#aaa;">#<?= str_pad($t['id'],5,'0',STR_PAD_LEFT) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($t['produce_name']) ?></strong><br>
                            <small style="color:#aaa;"><?= $t['category'] ?></small>
                        </td>
                        <td><?= $t['quantity'] ?> <?= $t['unit'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($t['trader_name']) ?></strong><br>
                            <small style="color:#aaa;">📞 <?= htmlspecialchars($t['trader_phone']) ?></small>
                        </td>
                        <td style="font-weight:700;color:var(--green-dark);font-size:16px;">
                            ₹<?= number_format($t['final_amount'], 2) ?>
                        </td>
                        <td style="color:#dc2626;">
                            - ₹<?= number_format($t['commission'], 2) ?>
                        </td>
                        <td style="font-weight:700;color:#2563eb;font-size:16px;">
                            ₹<?= number_format($t['final_amount'] - $t['commission'], 2) ?>
                        </td>
                        <td>
                            <?php if ($t['payment_status'] === 'paid'): ?>
                                <span class="badge badge-approved">✅ Paid</span>
                            <?php else: ?>
                                <span class="badge badge-pending">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;color:#888;"><?= date('d M Y', strtotime($t['transaction_date'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;padding:16px 20px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;font-size:14px;color:#15803d;">
                ℹ️ <strong>Note:</strong> APMC commission of <strong>2%</strong> is deducted from each sale. Net amount will be transferred to your registered bank account within 3-5 working days.
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
