<?php
require_once '../includes/config.php';
checkRole('admin');

$transactions = $conn->query("
    SELECT t.*, 
           l.produce_name, l.quantity, l.unit, l.category,
           uf.full_name as farmer_name, uf.phone as farmer_phone,
           ut.full_name as trader_name, ut.phone as trader_phone, ut.district as trader_district
    FROM transactions t
    JOIN listings l ON t.listing_id = l.id
    JOIN users uf ON t.farmer_id = uf.id
    JOIN users ut ON t.trader_id = ut.id
    ORDER BY t.transaction_date DESC
");

$totalValue  = $conn->query("SELECT COALESCE(SUM(final_amount),0) as r FROM transactions")->fetch_assoc()['r'];
$totalComm   = $conn->query("SELECT COALESCE(SUM(commission),0) as r FROM transactions")->fetch_assoc()['r'];
$totalCount  = $conn->query("SELECT COUNT(*) as c FROM transactions")->fetch_assoc()['c'];
$pendingPay  = $conn->query("SELECT COUNT(*) as c FROM transactions WHERE payment_status='pending'")->fetch_assoc()['c'];
$paidCount   = $conn->query("SELECT COUNT(*) as c FROM transactions WHERE payment_status='paid'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transactions | APMC Admin</title>
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
            <a href="bids.php"><span class="sidebar-nav-icon">🔨</span> All Bids</a>
            <a href="transactions.php" class="active"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <div class="sidebar-section-label">Reports</div>
            <a href="reports.php"><span class="sidebar-nav-icon">📈</span> Reports</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">All Transactions</div>
            <div class="topbar-right">
                <?php if ($pendingPay > 0): ?>
                <span style="background:#fef3c7;color:#d97706;padding:6px 14px;border-radius:999px;font-size:13px;font-weight:600;">⚠️ <?= $pendingPay ?> Payment Pending</span>
                <?php endif; ?>
                <button onclick="window.print()" class="btn-add" style="background:#555;">🖨️ Print</button>
            </div>
        </div>

        <div class="page-content">

            <div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
                <div class="stat-card"><div class="stat-card-icon green">✅</div><div><div class="stat-card-num"><?= $totalCount ?></div><div class="stat-card-label">Total Transactions</div></div></div>
                <div class="stat-card"><div class="stat-card-icon amber">💰</div><div><div class="stat-card-num">₹<?= number_format($totalValue,0) ?></div><div class="stat-card-label">Total Trade Value</div></div></div>
                <div class="stat-card"><div class="stat-card-icon blue">🏛️</div><div><div class="stat-card-num">₹<?= number_format($totalComm,0) ?></div><div class="stat-card-label">APMC Commission</div></div></div>
                <div class="stat-card"><div class="stat-card-icon <?= $pendingPay>0?'red':'green' ?>">
                    <?= $pendingPay>0?'⏳':'✅' ?></div><div><div class="stat-card-num"><?= $pendingPay ?></div><div class="stat-card-label">Pending Payments</div></div></div>
            </div>

            <!-- Info Box -->
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:14px 18px;margin-bottom:20px;font-size:14px;color:#1d4ed8;display:flex;gap:12px;align-items:center;">
                <span style="font-size:22px;">ℹ️</span>
                <span><strong>Payment System:</strong> Trader directly farmer ko payment karta hai website ke through. Yeh page sirf <strong>read-only record</strong> hai — APMC Officer ko koi action lene ki zarurat nahi.</span>
            </div>

            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">💰 Transaction Records (View Only)</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Txn ID</th>
                            <th>Produce</th>
                            <th>Farmer</th>
                            <th>Trader</th>
                            <th>Sale Amount</th>
                            <th>Commission</th>
                            <th>Payment Method</th>
                            <th>UTR / Ref No.</th>
                            <th>Payment Status</th>
                            <th>Payment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($transactions->num_rows === 0): ?>
                        <tr><td colspan="10" style="text-align:center;padding:40px;color:#aaa;">No transactions yet.</td></tr>
                    <?php else: ?>
                    <?php while ($t = $transactions->fetch_assoc()):
                        $utr    = $t['utr_number'] ?? null;
                        $pmethod = $t['payment_method'] ?? null;
                        $pdate  = $t['payment_date'] ?? null;
                    ?>
                    <tr style="<?= $t['payment_status']==='pending'?'background:#fffbeb;':'background:#f0fdf4;' ?>">
                        <td style="font-size:12px;color:#aaa;font-weight:600;">#<?= str_pad($t['id'],5,'0',STR_PAD_LEFT) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($t['produce_name']) ?></strong><br>
                            <small style="color:#aaa;"><?= $t['quantity'].' '.$t['unit'] ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($t['farmer_name']) ?><br>
                            <small style="color:#aaa;">📞 <?= $t['farmer_phone'] ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($t['trader_name']) ?><br>
                            <small style="color:#aaa;">📞 <?= $t['trader_phone'] ?></small>
                        </td>
                        <td style="font-weight:700;color:var(--green-dark);font-size:16px;">₹<?= number_format($t['final_amount'],2) ?></td>
                        <td style="color:#dc2626;">₹<?= number_format($t['commission'],2) ?></td>
                        <td>
                            <?php if ($pmethod): ?>
                                <span style="font-weight:600;font-size:13px;"><?= htmlspecialchars($pmethod) ?></span>
                            <?php else: ?>
                                <span style="color:#aaa;font-size:13px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($utr): ?>
                                <span style="font-weight:600;color:#2563eb;font-size:13px;"><?= htmlspecialchars($utr) ?></span>
                            <?php else: ?>
                                <span style="color:#aaa;font-size:13px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($t['payment_status']==='paid'): ?>
                                <span class="badge badge-approved">✅ Paid by Trader</span>
                            <?php else: ?>
                                <span class="badge badge-pending">⏳ Trader se Awaited</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;color:#888;">
                            <?= $pdate ? date('d M Y H:i', strtotime($pdate)) : '—' ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;padding:14px 18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;color:#64748b;">
                📋 <strong>Note:</strong> Trader website ke through directly farmer ko payment karta hai aur UTR number submit karta hai. Yeh records automatically yahan show hote hain. APMC Officer sirf monitor karta hai.
            </div>
        </div>
    </div>
</div>
</body>
</html>
