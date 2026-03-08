<?php
require_once 'includes/config.php';

if (isLoggedIn()) redirect('index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($conn, $_POST['full_name']);
    $email = sanitize($conn, $_POST['email']);
    $phone = sanitize($conn, $_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($conn, $_POST['role']);
    $address = sanitize($conn, $_POST['address']);
    $village = sanitize($conn, $_POST['village']);
    $district = sanitize($conn, $_POST['district']);
    $aadhar = sanitize($conn, $_POST['aadhar'] ?? '');
    $license = sanitize($conn, $_POST['license'] ?? '');

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'This email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role, address, village, district, aadhar_number, license_number) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssssss", $full_name, $email, $phone, $hashed, $role, $address, $village, $district, $aadhar, $license);
            if ($stmt->execute()) {
                $success = 'Registration successful! Your account is pending APMC officer approval. You will be notified once approved.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$preRole = $_GET['role'] ?? 'farmer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | APMC e-Trading System</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-panel-left">
        <div class="auth-brand-logo">🌾</div>
        <h2>Join APMC<br>e-Trading</h2>
        <p>Register as a Farmer or Trader to access Gujarat's most transparent agricultural marketplace.</p>
        <div class="auth-benefits">
            <div class="auth-benefit">
                <div class="auth-benefit-icon">✅</div>
                <span>Free registration — no hidden charges</span>
            </div>
            <div class="auth-benefit">
                <div class="auth-benefit-icon">⏱️</div>
                <span>Quick approval by APMC officer</span>
            </div>
            <div class="auth-benefit">
                <div class="auth-benefit-icon">🌐</div>
                <span>Trade from anywhere in Gujarat</span>
            </div>
        </div>
    </div>
    <div class="auth-panel-right" style="padding: 40px 60px; overflow-y: auto;">
        <div class="auth-form-wrap" style="max-width: 520px;">
            <a href="index.php" class="auth-back">← Back to Home</a>
            <h2>New Registration</h2>
            <p>Create your APMC e-Trading account</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <div class="auth-switch" style="margin-top:16px;"><a href="login.php">Go to Login →</a></div>
            <?php else: ?>

            <form method="POST">
                <div class="form-group">
                    <label>Register As</label>
                    <div class="role-selector" style="grid-template-columns: 1fr 1fr;">
                        <div class="role-option <?= $preRole === 'farmer' ? 'selected' : '' ?>" onclick="selectRole('farmer', this)">
                            <div class="role-option-icon">👨‍🌾</div>
                            <div class="role-option-name">Farmer</div>
                        </div>
                        <div class="role-option <?= $preRole === 'trader' ? 'selected' : '' ?>" onclick="selectRole('trader', this)">
                            <div class="role-option-icon">🤝</div>
                            <div class="role-option-name">Trader</div>
                        </div>
                    </div>
                    <input type="hidden" name="role" id="role" value="<?= htmlspecialchars($preRole) ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" placeholder="Ramesh Patel" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number *</label>
                        <input type="tel" name="phone" placeholder="9876543210" pattern="[0-9]{10}" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Village / Town *</label>
                    <input type="text" name="village" placeholder="Village name" required value="<?= htmlspecialchars($_POST['village'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>District *</label>
                        <select name="district" required>
                            <option value="">Select District</option>
                            <?php $districts = ['Ahmedabad','Surat','Vadodara','Rajkot','Bhavnagar','Anand','Kheda','Mehsana','Patan','Gandhinagar','Junagadh','Amreli','Jamnagar','Kutch','Banaskantha','Sabarkantha','Arvalli','Mahisagar','Chhota Udaipur','Dahod','Panchmahal','Narmada','Bharuch','Tapi','Navsari','Valsad','Dang'];
                            foreach ($districts as $d): ?>
                                <option value="<?= $d ?>" <?= (($_POST['district'] ?? '') === $d) ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Aadhar Number</label>
                        <input type="text" name="aadhar" placeholder="12-digit Aadhar" maxlength="12" value="<?= htmlspecialchars($_POST['aadhar'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group" id="license-group" style="display: none;">
                    <label>Trade License Number *</label>
                    <input type="text" name="license" placeholder="License number" value="<?= htmlspecialchars($_POST['license'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2" placeholder="Full address..."><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" placeholder="Min 6 characters" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password" placeholder="Repeat password" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Register Account</button>
            </form>
            <?php endif; ?>

            <div class="auth-switch">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</div>
<script>
function selectRole(role, el) {
    document.querySelectorAll('.role-option').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('role').value = role;
    document.getElementById('license-group').style.display = role === 'trader' ? 'block' : 'none';
}
// init
document.addEventListener('DOMContentLoaded', function() {
    const role = document.getElementById('role').value;
    if (role === 'trader') document.getElementById('license-group').style.display = 'block';
});
</script>
</body>
</html>
