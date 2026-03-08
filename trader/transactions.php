<?php
require_once '../includes/config.php';
checkRole('trader');
$traderId = $_SESSION['user_id'];

$success = '';
$error = '';

// Handle Payment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    $txn_id         = intval($_POST['txn_id']);
    $payment_method = sanitize($conn, $_POST['payment_method']);
    $utr_number     = sanitize($conn, $_POST['utr_number']);
    $payment_note   = sanitize($conn, $_POST['payment_note']);

    $check = $conn->query("SELECT * FROM transactions WHERE id=$txn_id AND trader_id=$traderId AND payment_status='pending'")->fetch_assoc();

    if (!$check) {
        $error = 'Invalid transaction ya already paid hai.';
    } elseif (!$utr_number) {
        $error = 'Please UTR / Reference number daalo.';
    } else {
        $payment_date = date('Y-m-d H:i:s');
        // Try with extra columns first, fallback to basic update
        $stmt = $conn->prepare("UPDATE transactions SET payment_status='paid', payment_method=?, utr_number=?, payment_note=?, payment_date=? WHERE id=? AND trader_id=?");
        if ($stmt) {
            $stmt->bind_param("ssssii", $payment_method, $utr_number, $payment_note, $payment_date, $txn_id, $traderId);
            $stmt->execute();
        } else {
            // Fallback if columns don't exist yet
            $conn->query("UPDATE transactions SET payment_status='paid' WHERE id=$txn_id AND trader_id=$traderId");
        }
        $success = '✅ Payment successfully recorded! Farmer ko notify kar diya gaya hai. APMC officer ke dashboard mein bhi record ho gaya.';
    }
}

// Fetch transactions
$transactions = $conn->query("
    SELECT t.*, 
           l.produce_name, l.quantity, l.unit, l.category,
           u.full_name as farmer_name, u.phone as farmer_phone, 
           u.village as farmer_village, u.district as farmer_district
    FROM transactions t
    JOIN listings l ON t.listing_id = l.id
    JOIN users u ON t.farmer_id = u.id
    WHERE t.trader_id = $traderId
    ORDER BY t.transaction_date DESC
");

$totalSpent = $conn->query("SELECT COALESCE(SUM(final_amount),0) as r FROM transactions WHERE trader_id=$traderId")->fetch_assoc()['r'];
$totalCount = $conn->query("SELECT COUNT(*) as c FROM transactions WHERE trader_id=$traderId")->fetch_assoc()['c'];
$pendingPay = $conn->query("SELECT COUNT(*) as c FROM transactions WHERE trader_id=$traderId AND payment_status='pending'")->fetch_assoc()['c'];
$paidCount  = $conn->query("SELECT COUNT(*) as c FROM transactions WHERE trader_id=$traderId AND payment_status='paid'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions & Payments | APMC e-Trading</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .payment-modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; }
        .payment-modal.open { display:flex; }
        .modal-box { background:white; border-radius:20px; padding:36px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; animation:slideUp 0.3s ease; }
        @keyframes slideUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; padding-bottom:16px; border-bottom:2px solid var(--cream-dark); }
        .modal-title { font-family:var(--font-display); font-size:22px; color:var(--green-dark); }
        .modal-close { width:36px; height:36px; background:var(--cream-dark); border:none; border-radius:50%; cursor:pointer; font-size:18px; }
        .pm-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-bottom:20px; }
        .pm-option { border:2px solid rgba(0,0,0,0.1); border-radius:12px; padding:14px; text-align:center; cursor:pointer; transition:all 0.2s; }
        .pm-option.selected { border-color:var(--green-light); background:rgba(74,140,63,0.08); }
        .pm-icon { font-size:28px; margin-bottom:6px; }
        .pm-name { font-size:13px; font-weight:600; }
        .farmer-box { background:var(--cream-dark); border-radius:12px; padding:16px; margin-bottom:20px; }
        .farmer-box h4 { font-size:14px; color:var(--green-dark); margin-bottom:10px; font-weight:700; }
        .frow { display:flex; justify-content:space-between; font-size:14px; padding:5px 0; border-bottom:1px solid rgba(0,0,0,0.05); }
        .frow:last-child { border:none; }
        .frow span:first-child { color:#888; }
        .frow span:last-child { font-weight:600; }
        .amount-box { background:var(--green-dark); color:white; border-radius:12px; padding:16px 20px; text-align:center; margin-bottom:20px; }
        .amount-box .alabel { font-size:12px; opacity:0.7; text-transform:uppercase; letter-spacing:1px; }
        .amount-box .avalue { font-family:var(--font-display); font-size:36px; font-weight:700; color:var(--amber-light); }
    </style>
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar">
        <div class="sidebar-brand"><div class="sidebar-brand-name">🌾 APMC</div><div class="sidebar-brand-sub">e-Trading System</div></div>
        <div class="sidebar-user">
            <div class="sidebar-avatar">🤝</div>
            <div><div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div><div class="sidebar-user-role">Trader</div></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="dashboard.php"><span class="sidebar-nav-icon">📊</span> Dashboard</a>
            <a href="browse-listings.php"><span class="sidebar-nav-icon">🌾</span> Browse Listings</a>
            <a href="my-bids.php"><span class="sidebar-nav-icon">🔨</span> My Bids</a>
            <div class="sidebar-section-label">Account</div>
            <a href="transactions.php" class="active"><span class="sidebar-nav-icon">💰</span> Transactions</a>
            <a href="profile.php"><span class="sidebar-nav-icon">👤</span> Profile</a>
        </nav>
        <div class="sidebar-logout"><a href="../logout.php">🚪 Logout</a></div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">Transactions & Payments</div>
            <div class="topbar-right">
                <?php if ($pendingPay > 0): ?>
                <span style="background:#fef3c7;color:#d97706;padding:6px 14px;border-radius:999px;font-size:13px;font-weight:600;">⚠️ <?= $pendingPay ?> Pending</span>
                <?php endif; ?>
                <button onclick="window.print()" class="btn-add" style="background:#555;">🖨️ Print</button>
            </div>
        </div>

        <div class="page-content">

            <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

            <div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
                <div class="stat-card"><div class="stat-card-icon green">🛒</div><div><div class="stat-card-num"><?= $totalCount ?></div><div class="stat-card-label">Total Purchases</div></div></div>
                <div class="stat-card"><div class="stat-card-icon amber">💰</div><div><div class="stat-card-num">₹<?= number_format($totalSpent,0) ?></div><div class="stat-card-label">Total Spent</div></div></div>
                <div class="stat-card"><div class="stat-card-icon blue">✅</div><div><div class="stat-card-num"><?= $paidCount ?></div><div class="stat-card-label">Payments Done</div></div></div>
                <div class="stat-card"><div class="stat-card-icon red">⏳</div><div><div class="stat-card-num"><?= $pendingPay ?></div><div class="stat-card-label">Pending Payments</div></div></div>
            </div>

            <?php if ($pendingPay > 0): ?>
            <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:12px;padding:16px 20px;margin-bottom:20px;font-size:14px;color:#92400e;display:flex;align-items:center;gap:12px;">
                <span style="font-size:28px;">⚠️</span>
                <div><strong><?= $pendingPay ?> payment(s) pending hain!</strong><br>
                Neeche <strong>"💳 Pay Now"</strong> button click karo aur seedha farmer ko payment karo. APMC officer ko contact karne ki zarurat nahi!</div>
            </div>
            <?php endif; ?>

            <?php if ($transactions->num_rows === 0): ?>
                <div class="alert alert-info">🛒 Koi purchase nahi hua abhi. <a href="browse-listings.php">Listings browse karo →</a></div>
            <?php else: ?>

            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">💰 Purchase & Payment History</div>
                </div>
                <table>
                    <thead>
                        <tr><th>Txn ID</th><th>Produce</th><th>Farmer</th><th>Amount</th><th>Payment Method</th><th>UTR / Ref No.</th><th>Status</th><th>Date</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php while ($t = $transactions->fetch_assoc()):
                        $utr = $t['utr_number'] ?? null;
                        $pmethod = $t['payment_method'] ?? null;
                        $pdate = $t['payment_date'] ?? null;
                    ?>
                    <tr style="<?= $t['payment_status']==='pending'?'background:#fffbeb;':'background:#f0fdf4;' ?>">
                        <td style="font-size:12px;color:#aaa;font-weight:600;">#<?= str_pad($t['id'],5,'0',STR_PAD_LEFT) ?></td>
                        <td><strong><?= htmlspecialchars($t['produce_name']) ?></strong><br><small style="color:#aaa;"><?= $t['quantity'].' '.$t['unit'] ?></small></td>
                        <td>
                            <strong><?= htmlspecialchars($t['farmer_name']) ?></strong><br>
                            <small style="color:#16a34a;font-weight:600;">📞 <?= htmlspecialchars($t['farmer_phone']) ?></small><br>
                            <small style="color:#aaa;">📍 <?= htmlspecialchars(($t['farmer_village']??'').($t['farmer_district']?', '.$t['farmer_district']:'')) ?></small>
                        </td>
                        <td style="font-size:18px;font-weight:700;color:var(--green-dark);">₹<?= number_format($t['final_amount'],2) ?></td>
                        <td><?= $pmethod ? htmlspecialchars($pmethod) : '<span style="color:#aaa;">—</span>' ?></td>
                        <td style="font-size:13px;font-weight:600;color:#2563eb;"><?= $utr ? htmlspecialchars($utr) : '<span style="color:#aaa;">—</span>' ?></td>
                        <td>
                            <?php if ($t['payment_status']==='paid'): ?>
                                <span class="badge badge-approved">✅ Paid</span>
                                <?php if ($pdate): ?><br><small style="color:#888;font-size:11px;"><?= date('d M Y H:i',strtotime($pdate)) ?></small><?php endif; ?>
                            <?php else: ?>
                                <span class="badge badge-pending">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;color:#888;"><?= date('d M Y',strtotime($t['transaction_date'])) ?></td>
                        <td>
                            <?php if ($t['payment_status']==='pending'): ?>
                            <button onclick="openModal(
                                <?= $t['id'] ?>,
                                '<?= addslashes(htmlspecialchars($t['farmer_name'])) ?>',
                                '<?= $t['farmer_phone'] ?>',
                                '<?= addslashes(htmlspecialchars($t['produce_name'])) ?>',
                                '<?= $t['quantity'].' '.$t['unit'] ?>',
                                '<?= number_format($t['final_amount'],2) ?>'
                            )" class="btn-sm btn-sm-green" style="padding:8px 16px;">💳 Pay Now</button>
                            <?php else: ?>
                            <span style="font-size:12px;color:#16a34a;font-weight:600;">✅ Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- PAYMENT MODAL -->
<div class="payment-modal" id="payModal">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">💳 Farmer ko Payment Karo</div>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>

        <div class="farmer-box">
            <h4>👨‍🌾 Farmer Details — Inhe Directly Pay Karo</h4>
            <div class="frow"><span>Farmer Name</span><span id="m-farmer">—</span></div>
            <div class="frow"><span>Mobile No.</span><span id="m-phone" style="color:#16a34a;font-size:16px;">—</span></div>
            <div class="frow"><span>Produce</span><span id="m-produce">—</span></div>
            <div class="frow"><span>Quantity</span><span id="m-qty">—</span></div>
        </div>

        <div class="amount-box">
            <div class="alabel">Total Amount to Pay Farmer</div>
            <div class="avalue">₹<span id="m-amount">0</span></div>
        </div>

        <form method="POST">
            <input type="hidden" name="submit_payment" value="1">
            <input type="hidden" name="txn_id" id="m-txn-id" value="">

            <div class="form-group">
                <label>Payment Method *</label>
                <div class="pm-grid">
                    <div class="pm-option selected" onclick="selPM('UPI / GPay / PhonePe', this)">
                        <div class="pm-icon">📱</div><div class="pm-name">UPI / GPay</div>
                    </div>
                    <div class="pm-option" onclick="selPM('NEFT / RTGS', this)">
                        <div class="pm-icon">🏦</div><div class="pm-name">NEFT / RTGS</div>
                    </div>
                    <div class="pm-option" onclick="selPM('IMPS', this)">
                        <div class="pm-icon">⚡</div><div class="pm-name">IMPS</div>
                    </div>
                    <div class="pm-option" onclick="selPM('Cash', this)">
                        <div class="pm-icon">💵</div><div class="pm-name">Cash</div>
                    </div>
                </div>
                <input type="hidden" name="payment_method" id="pm-input" value="UPI / GPay / PhonePe">
            </div>

            <div class="form-group">
                <label>UTR / Transaction Reference Number *</label>
                <input type="text" name="utr_number" placeholder="UPI Ref No. ya Bank UTR number" required>
                <small style="color:#888;font-size:12px;">Cash payment ke liye: "CASH-DATE" likhein, jaise CASH-08032026</small>
            </div>

            <div class="form-group">
                <label>Note (Optional)</label>
                <textarea name="payment_note" rows="2" placeholder="Koi additional note..."></textarea>
            </div>

            <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#15803d;">
                ✅ Yeh payment details automatically APMC Officer ke dashboard mein record ho jaayenge.
            </div>

            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-submit" style="flex:1;margin-top:0;">✅ Payment Confirm Karo</button>
                <button type="button" onclick="closeModal()" style="padding:14px 20px;background:#f1f5f9;border:none;border-radius:10px;cursor:pointer;font-weight:600;">Pay Later</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id, farmer, phone, produce, qty, amount) {
    document.getElementById('m-txn-id').value = id;
    document.getElementById('m-farmer').textContent = farmer;
    document.getElementById('m-phone').textContent = '📞 ' + phone;
    document.getElementById('m-produce').textContent = produce;
    document.getElementById('m-qty').textContent = qty;
    document.getElementById('m-amount').textContent = amount;
    document.getElementById('payModal').classList.add('open');
}
function closeModal() {
    document.getElementById('payModal').classList.remove('open');
}
function selPM(method, el) {
    document.querySelectorAll('.pm-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('pm-input').value = method;
}
document.getElementById('payModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>