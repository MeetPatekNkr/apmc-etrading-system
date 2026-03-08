<?php
require_once '../includes/config.php';
checkRole('admin');

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $action = $_POST['action'];
    if ($action === 'approve') {
        $conn->query("UPDATE users SET is_approved=1 WHERE id=$id");
    } elseif ($action === 'unapprove') {
        $conn->query("UPDATE users SET is_approved=0 WHERE id=$id");
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM users WHERE id=$id AND role!='admin'");
    }
    header("Location: users.php");
    exit();
}

$filter = $_GET['filter'] ?? 'all';
$where = "role != 'admin'";
if ($filter === 'farmer') $where .= " AND role='farmer'";
elseif ($filter === 'trader') $where .= " AND role='trader'";
elseif ($filter === 'pending') $where .= " AND is_approved=0";

$users = $conn->query("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
$totalFarmers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='farmer'")->fetch_assoc()['c'];
$totalTraders = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='trader'")->fetch_assoc()['c'];
$pendingCount = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_approved=0 AND role!='admin'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users | APMC Admin</title>
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
            <a href="users.php" class="active"><span class="sidebar-nav-icon">👥</span> All Users</a>
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
            <div class="topbar-title">All Users</div>
        </div>
        <div class="page-content">
            <!-- Stats -->
            <div class="stats-row" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
                <div class="stat-card"><div class="stat-card-icon green">👨‍🌾</div><div><div class="stat-card-num"><?= $totalFarmers ?></div><div class="stat-card-label">Total Farmers</div></div></div>
                <div class="stat-card"><div class="stat-card-icon amber">🤝</div><div><div class="stat-card-num"><?= $totalTraders ?></div><div class="stat-card-label">Total Traders</div></div></div>
                <div class="stat-card"><div class="stat-card-icon red">⏳</div><div><div class="stat-card-num"><?= $pendingCount ?></div><div class="stat-card-label">Pending Approval</div></div></div>
            </div>

            <!-- Filter Tabs -->
            <div style="display:flex;gap:10px;margin-bottom:20px;">
                <a href="users.php?filter=all" class="btn-sm <?= $filter==='all'?'btn-sm-green':'btn-sm-blue' ?>" style="padding:10px 20px;">All Users</a>
                <a href="users.php?filter=farmer" class="btn-sm <?= $filter==='farmer'?'btn-sm-green':'btn-sm-blue' ?>" style="padding:10px 20px;">👨‍🌾 Farmers</a>
                <a href="users.php?filter=trader" class="btn-sm <?= $filter==='trader'?'btn-sm-green':'btn-sm-blue' ?>" style="padding:10px 20px;">🤝 Traders</a>
                <a href="users.php?filter=pending" class="btn-sm <?= $filter==='pending'?'btn-sm-amber':'btn-sm-blue' ?>" style="padding:10px 20px;">⏳ Pending (<?= $pendingCount ?>)</a>
            </div>

            <div class="data-table-wrap">
                <div class="data-table-header">
                    <div class="data-table-title">👥 User List</div>
                </div>
                <table>
                    <thead>
                        <tr><th>#</th><th>Name</th><th>Role</th><th>Email</th><th>Phone</th><th>District</th><th>Aadhar</th><th>Status</th><th>Registered</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($users->num_rows === 0): ?>
                        <tr><td colspan="10" style="text-align:center;padding:40px;color:#aaa;">No users found.</td></tr>
                    <?php else: ?>
                    <?php $sr=1; while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $sr++ ?></td>
                        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong><br><small style="color:#aaa;"><?= htmlspecialchars($u['village']??'') ?></small></td>
                        <td><span class="badge <?= $u['role']==='farmer'?'badge-active':'badge-pending' ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td style="font-size:13px;"><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td><?= htmlspecialchars($u['district']??'N/A') ?></td>
                        <td style="font-size:13px;"><?= htmlspecialchars($u['aadhar_number']??'N/A') ?></td>
                        <td>
                            <?php if ($u['is_approved']): ?>
                                <span class="badge badge-approved">✅ Approved</span>
                            <?php else: ?>
                                <span class="badge badge-pending">⏳ Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:13px;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td class="action-btns">
                            <?php if (!$u['is_approved']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn-sm btn-sm-green" onclick="return confirm('Approve this user?')">✅ Approve</button>
                            </form>
                            <?php else: ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="unapprove">
                                <button type="submit" class="btn-sm btn-sm-amber" onclick="return confirm('Suspend this user?')">⏸ Suspend</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-sm btn-sm-red" onclick="return confirm('Delete this user permanently?')">🗑 Delete</button>
                            </form>
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
