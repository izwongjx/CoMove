<?php
include "src/config/conn.php";

function homeCount(mysqli $dbConn, string $sql): int
{
    $result = mysqli_query($dbConn, $sql);
    if (!$result) {
        return 0;
    }
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return (int) ($row['total'] ?? 0);
}

$totalRiders = homeCount($dbConn, 'SELECT COUNT(*) AS total FROM RIDER');
$totalDrivers = homeCount($dbConn, 'SELECT COUNT(*) AS total FROM DRIVER');
$totalTrips = homeCount($dbConn, 'SELECT COUNT(*) AS total FROM TRIP');
$completedTrips = homeCount($dbConn, "SELECT COUNT(*) AS total FROM TRIP WHERE trip_status = 'completed'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoMove - Every Ride Counts Towards Change</title>
    <link rel="icon" type="image/svg+xml" href="src/public-assets/icons/site-icon.svg">
    <link rel="stylesheet" href="src/public-assets/style.css">
    <link rel="stylesheet" href="src/auth/index.css">
</head>

<body>
    <!-- HERO SECTION -->
    <section class="hero" id="hero">
        <nav class="hero-nav">
            <div class="hero-logo">CO<span>MOVE</span></div>
            <div class="hero-nav-links">
                <a href="src/auth/login/login.php" class="nav-link-login">Log In</a>
                <a href="src/auth/register/register.php" class="nav-link-signup">Sign Up</a>
            </div>
        </nav>

        <div class="hero-content">
            <div class="hero-text">
                <h1>Every Ride <span>Counts</span> Towards Change</h1>
                <p>Join sharing journeys daily. The planet can't wait for empty seats.</p>
            </div>

            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-value"><?php echo number_format($totalRiders); ?></div>
                    <div class="hero-stat-label">Riders</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value"><?php echo number_format($totalDrivers); ?></div>
                    <div class="hero-stat-label">Drivers</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-value"><?php echo number_format($totalTrips); ?></div>
                    <div class="hero-stat-label">Total Rides</div>
                </div>
            </div>

            <div class="hero-cta">
                <a href="src/auth/register/register.php" class="hero-cta-btn">
                    START RIDING NOW
                    <img src="src/public-assets/icons/arrow-right.svg" width="24" height="24" class="icon-img" alt="this is an arrow icon">
                </a>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features" id="features">
        <div class="features-header">
            <h2>Why We <span>Ride</span></h2>
            <div class="features-bar"></div>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-top-bar"></div>
                <div class="feature-icon"><img src="src/public-assets/icons/leaf.svg" width="32" height="32" class="icon-img" alt="" aria-hidden="true"></div>
                <h3>Reduce Carbon Footprint</h3>
                <p>Every shared mile cuts CO2 emissions by 50%. <br> Your daily commute becomes an act of environmental
                    resistance.</p>
            </div>

            <div class="feature-card">
                <div class="feature-top-bar"></div>
                <div class="feature-icon"><img src="src/public-assets/icons/wallet.svg" width="32" height="32" class="icon-img" alt="" aria-hidden="true"></div>
                <h3>Save Money Together</h3>
                <p>Split costs, not just rides. <br> Cut your commuting expenses in half while investing in a cleaner
                    future.</p>
            </div>

            <div class="feature-card">
                <div class="feature-top-bar"></div>
                <div class="feature-icon"><img src="src/public-assets/icons/users.svg" width="32" height="32" class="icon-img" alt="" aria-hidden="true"></div>
                <h3>Build Community</h3>
                <p>Connect with like-minded activists in your area. <br> Turn isolation into collective action.</p>
            </div>
        </div>
    </section>

    <!-- STATS SECTION -->
    <section class="stats-section" id="stats">
        <div class="stats-inner">
            <div class="stats-header" data-animate>
                <h2>Our Collective Impact</h2>
                <div class="stats-header-bar"></div>
            </div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($completedTrips); ?></div>
                    <div class="stat-label">Trips Completed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($totalRiders); ?></div>
                    <div class="stat-label">Total Riders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($totalTrips); ?></div>
                    <div class="stat-label">Rides Shared</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta-section" id="cta">
        <div class="cta-inner">
            <div class="cta-badge"><img src="src/public-assets/icons/globe.svg" width="20" height="20" class="icon-img" alt=""><span>Global Initiative</span>
            </div>
            <h2>THE CLIMATE CRISIS<br>WON'T WAIT. <span>WILL YOU?</span></h2>
            <p>Every empty seat is a missed opportunity to save our planet. Take action today and turn your commute into
                a movement.</p>
            <div class="cta-buttons">
                <a href="src/auth/register/register.php" class="cta-main-btn">JOIN THE MOVEMENT <img src="src/public-assets/icons/arrow-right.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-logo">CO<span>MOVE</span></div>
            <div class="footer-copy">&copy; 2026 CoMove Initiative. All rights reserved.</div>
        </div>
    </footer>

    <script src="src/public-assets/script.js"></script>
</body>

</html>







