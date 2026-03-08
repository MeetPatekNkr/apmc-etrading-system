<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APMC e-Trading System | Agricultural Produce Market</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-brand">
            <div class="brand-icon">🌾</div>
            <div class="brand-text">
                <span class="brand-name">APMC</span>
                <span class="brand-sub">e-Trading System</span>
            </div>
        </div>
        <div class="nav-links">
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#features">Features</a>
            <a href="login.php" class="btn-nav-login">Login</a>
            <a href="register.php" class="btn-nav-register">Register</a>
        </div>
        <button class="hamburger" id="hamburger">☰</button>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero-bg">
        <div class="grain-overlay"></div>
        <div class="hero-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>
    <div class="hero-content">
        <div class="hero-badge">🏛️ Government of Gujarat</div>
        <h1 class="hero-title">
            Agricultural Produce<br>
            <span class="hero-highlight">Market Committee</span><br>
            Electronic Trading System
        </h1>
        <p class="hero-desc">A transparent digital platform where farmers list their produce and registered traders bid competitively — ensuring fair prices and a corruption-free market.</p>
        <div class="hero-cta">
            <a href="register.php?role=farmer" class="cta-primary">Register as Farmer</a>
            <a href="register.php?role=trader" class="cta-secondary">Register as Trader</a>
        </div>
        <div class="hero-stats">
            <div class="stat">
                <span class="stat-num">2,400+</span>
                <span class="stat-label">Farmers</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num">850+</span>
                <span class="stat-label">Traders</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat">
                <span class="stat-num">₹48Cr+</span>
                <span class="stat-label">Traded Value</span>
            </div>
        </div>
    </div>
    <div class="hero-visual">
        <div class="visual-card card-float-1">
            <div class="vc-icon">🌾</div>
            <div class="vc-text">Wheat — 500 Qtl</div>
            <div class="vc-price">₹2,450/Qtl</div>
            <div class="vc-status active">Live Bidding</div>
        </div>
        <div class="visual-card card-float-2">
            <div class="vc-icon">🌿</div>
            <div class="vc-text">Cotton — 200 Qtl</div>
            <div class="vc-price">₹6,200/Qtl</div>
            <div class="vc-status bidding">3 Bids</div>
        </div>
        <div class="visual-card card-float-3">
            <div class="vc-icon">🧅</div>
            <div class="vc-text">Onion — 800 Qtl</div>
            <div class="vc-price">₹1,850/Qtl</div>
            <div class="vc-status sold">Sold</div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section" id="about">
    <div class="container">
        <div class="section-label">About the Platform</div>
        <h2 class="section-title">Empowering Farmers.<br>Enabling Fair Trade.</h2>
        <div class="about-grid">
            <div class="about-text">
                <p>The APMC e-Trading System is an online, web-based digital platform developed to facilitate the direct sale of agricultural produce by farmers. Farmers can list their goods such as grain, cotton, onions, and more — and registered traders can participate in a transparent online bidding process.</p>
                <p>The system eliminates middlemen, reduces corruption, and ensures farmers receive competitive and fair market prices for their hard-earned produce.</p>
                <div class="about-users">
                    <div class="user-card">
                        <div class="user-icon">👨‍🌾</div>
                        <div class="user-name">Farmer</div>
                        <div class="user-desc">List produce & receive fair bids</div>
                    </div>
                    <div class="user-card">
                        <div class="user-icon">🤝</div>
                        <div class="user-name">Trader</div>
                        <div class="user-desc">Bid on quality agricultural produce</div>
                    </div>
                    <div class="user-card">
                        <div class="user-icon">🏛️</div>
                        <div class="user-name">APMC Officer</div>
                        <div class="user-desc">Oversee & manage all operations</div>
                    </div>
                </div>
            </div>
            <div class="about-visual">
                <div class="process-flow">
                    <div class="process-step">
                        <div class="ps-num">01</div>
                        <div class="ps-text">Farmer lists produce online</div>
                    </div>
                    <div class="process-arrow">↓</div>
                    <div class="process-step">
                        <div class="ps-num">02</div>
                        <div class="ps-text">APMC Officer approves listing</div>
                    </div>
                    <div class="process-arrow">↓</div>
                    <div class="process-step">
                        <div class="ps-num">03</div>
                        <div class="ps-text">Traders place competitive bids</div>
                    </div>
                    <div class="process-arrow">↓</div>
                    <div class="process-step">
                        <div class="ps-num">04</div>
                        <div class="ps-text">Highest bidder wins — sale recorded</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section" id="features">
    <div class="container">
        <div class="section-label">Platform Features</div>
        <h2 class="section-title">Everything You Need<br>For Transparent Trade</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feat-icon">📝</div>
                <h3>Online Registration</h3>
                <p>Farmers and Traders can register digitally. APMC officer reviews and approves all accounts ensuring authenticity.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">🔨</div>
                <h3>Digital Bidding</h3>
                <p>Real-time online auction system where traders compete for the best produce. Highest bidder wins automatically.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">👁️</div>
                <h3>Full Transparency</h3>
                <p>All bids are visible to participants. No hidden dealings. Every transaction is digitally recorded and auditable.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">📊</div>
                <h3>Digital Records</h3>
                <p>Complete history of listings, bids, and sales. Reduces paper documentation and enables easy record maintenance.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">🔔</div>
                <h3>Live Notifications</h3>
                <p>Get instant alerts when someone bids on your produce, when a bid is won, or when approval is needed.</p>
            </div>
            <div class="feature-card">
                <div class="feat-icon">🛡️</div>
                <h3>Secure & Verified</h3>
                <p>All users are verified through Aadhar and license numbers. Role-based access ensures data security.</p>
            </div>
        </div>
    </div>
</section>

<!-- Advantages Section -->
<section class="advantages-section">
    <div class="container">
        <div class="section-label">Why APMC e-Trading?</div>
        <h2 class="section-title">Advantages of the System</h2>
        <div class="adv-grid">
            <div class="adv-item">
                <div class="adv-icon">💰</div>
                <div class="adv-content">
                    <h4>Competitive & Fair Prices</h4>
                    <p>Multiple traders bid, driving prices up — farmers always get the best market value.</p>
                </div>
            </div>
            <div class="adv-item">
                <div class="adv-icon">⚡</div>
                <div class="adv-content">
                    <h4>Saves Time & Paper</h4>
                    <p>Entire process is digital — from registration to payment. No queues, no paperwork.</p>
                </div>
            </div>
            <div class="adv-item">
                <div class="adv-icon">🔍</div>
                <div class="adv-content">
                    <h4>Minimizes Corruption</h4>
                    <p>Greater transparency eliminates manipulation. Every bid is publicly logged and audited.</p>
                </div>
            </div>
            <div class="adv-item">
                <div class="adv-icon">💾</div>
                <div class="adv-content">
                    <h4>Easy Record Maintenance</h4>
                    <p>All data stored digitally. Access reports, history, and analytics anytime, anywhere.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-box">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of farmers and traders already benefiting from transparent digital trading.</p>
            <div class="cta-buttons">
                <a href="register.php" class="cta-primary">Register Now — It's Free</a>
                <a href="login.php" class="cta-outline">Already Registered? Login</a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">🌾 APMC e-Trading</div>
                <p>Agricultural Produce Market Committee<br>Electronic Trading System<br>Government of Gujarat</p>
            </div>
            <div class="footer-links">
                <h4>Quick Links</h4>
                <a href="login.php">Farmer Login</a>
                <a href="login.php">Trader Login</a>
                <a href="register.php">New Registration</a>
            </div>
            <div class="footer-links">
                <h4>Produce Categories</h4>
                <a href="#">Grains & Cereals</a>
                <a href="#">Cotton</a>
                <a href="#">Vegetables & Fruits</a>
                <a href="#">Spices & Oilseeds</a>
            </div>
            <div class="footer-links">
                <h4>Contact</h4>
                <p>📍 APMC Market Yard, Ahmedabad, Gujarat</p>
                <p>📞 1800-XXX-XXXX (Toll Free)</p>
                <p>✉️ support@apmc.gov.in</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 APMC e-Trading System | Government of Gujarat. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
