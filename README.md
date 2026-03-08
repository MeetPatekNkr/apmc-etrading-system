# APMC e-Trading System
## Agricultural Produce Market Committee – Electronic Trading System

A full-stack web application for transparent digital trading of agricultural produce.

---

## 🛠️ Technology Stack

| Layer      | Technology                    |
|------------|-------------------------------|
| Frontend   | HTML5, CSS3, JavaScript (ES6) |
| Backend    | PHP 8.x                       |
| Database   | MySQL 8.x                     |
| Web Server | Apache / XAMPP / WAMP         |

---

## 👥 User Roles

| Role | Description |
|------|-------------|
| **Farmer** | Register, list produce for auction, view bids received |
| **Trader** | Register, browse live auctions, place competitive bids |
| **APMC Officer (Admin)** | Approve users & listings, monitor auctions, close bids |

---

## 📁 Project Structure

```
apmc/
├── index.php              ← Home / Landing Page
├── login.php              ← Login for all roles
├── register.php           ← Registration for Farmers & Traders
├── logout.php             ← Session destroy
├── database.sql           ← Full MySQL database schema
│
├── includes/
│   └── config.php         ← DB connection, session helpers
│
├── css/
│   └── style.css          ← All styling
│
├── js/
│   ├── main.js            ← Navigation & animations
│   └── countdown.js       ← Live auction countdown timers
│
├── farmer/
│   ├── dashboard.php      ← Farmer overview & stats
│   ├── add-listing.php    ← Submit new produce listing
│   ├── my-listings.php    ← All farmer's listings
│   ├── view-bids.php      ← Bids on a specific listing
│   ├── my-bids.php        ← All bids overview
│   ├── transactions.php   ← Completed sales
│   └── profile.php        ← Edit profile
│
├── trader/
│   ├── dashboard.php      ← Live auctions + quick bidding
│   ├── browse-listings.php← Search & filter listings
│   ├── my-bids.php        ← Trader's bid history
│   ├── transactions.php   ← Purchases
│   └── profile.php        ← Edit profile
│
└── admin/
    ├── dashboard.php      ← Admin overview + approvals
    ├── users.php          ← Manage all users
    ├── listings.php       ← Manage all listings
    ├── bids.php           ← All bids
    ├── transactions.php   ← All transactions
    └── reports.php        ← Analytics & reports
```

---

## ⚙️ Setup Instructions

### Step 1: Install XAMPP / WAMP
Download and install XAMPP from https://www.apachefriends.org

### Step 2: Copy Project Files
```
Copy the entire `apmc/` folder to:
C:\xampp\htdocs\apmc\        (Windows)
/opt/lampp/htdocs/apmc/      (Linux)
```

### Step 3: Create Database
1. Start Apache and MySQL in XAMPP Control Panel
2. Open: http://localhost/phpmyadmin
3. Click "New" → Create database: `apmc_trading`
4. Click "Import" → Select `database.sql` → Click "Go"

### Step 4: Configure Database
Edit `includes/config.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Your MySQL username
define('DB_PASS', '');           // Your MySQL password
define('DB_NAME', 'apmc_trading');
```

### Step 5: Open in Browser
```
http://localhost/apmc/
```

---

## 🔑 Default Admin Login

```
Email:    admin@apmc.gov.in
Password: Admin@123
Role:     APMC Officer
```

---

## 🔄 Workflow

```
1. Farmer registers → APMC approves account
2. Farmer adds produce listing → APMC reviews & approves
3. Listing goes LIVE → Traders place bids
4. Bidding period ends → APMC closes auction
5. Highest bidder WINS → Transaction recorded
6. Farmer & Trader notified → Payment processed
```

---

## ✨ Key Features

- ✅ Online registration for Farmers & Traders
- ✅ APMC officer approval workflow
- ✅ Live digital bidding with real-time countdown
- ✅ Transparent bid history (all bids visible)
- ✅ Role-based dashboards (Farmer / Trader / Admin)
- ✅ Transaction records & revenue tracking
- ✅ Responsive design (mobile-friendly)
- ✅ Category & search filtering for listings
- ✅ Commission calculation on sales

---

## 📞 Support

APMC e-Trading System | Government of Gujarat
Toll Free: 1800-XXX-XXXX
Email: support@apmc.gov.in
