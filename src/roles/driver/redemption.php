<?php
session_start();
include "../../config/conn.php";

$role = isset($_SESSION['role']) ? strtolower(trim((string) $_SESSION['role'])) : '';
$driverId = isset($_SESSION['user_id']) ? trim((string) $_SESSION['user_id']) : '';

if ($role !== 'driver' || $driverId === '') {
    echo "<script>alert('Please login as driver first.');";
    die("window.location.href='../../auth/login/login.php';</script>");
}

$driverStatusStmt = mysqli_prepare($dbConn, 'SELECT driver_status FROM DRIVER WHERE driver_id = ? LIMIT 1');
if (!$driverStatusStmt) {
    echo "<script>alert('Unable to verify your account right now.');";
    die("window.location.href='../../auth/login/login.php';</script>");
}

$driverIdInt = (int) $driverId;
mysqli_stmt_bind_param($driverStatusStmt, 'i', $driverIdInt);
mysqli_stmt_execute($driverStatusStmt);
$driverStatusResult = mysqli_stmt_get_result($driverStatusStmt);
$driverStatusRow = $driverStatusResult ? mysqli_fetch_assoc($driverStatusResult) : null;
if ($driverStatusResult) {
    mysqli_free_result($driverStatusResult);
}
mysqli_stmt_close($driverStatusStmt);

$driverStatus = strtolower(trim((string) ($driverStatusRow['driver_status'] ?? '')));
if ($driverStatus !== 'active') {
    session_unset();
    session_destroy();
    echo "<script>alert('This driver account is currently banned. Please contact an admin.');";
    die("window.location.href='../../auth/login/login.php';</script>");
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$driverPoints = 0;
$pointsStmt = mysqli_prepare($dbConn, 'SELECT COALESCE(SUM(points_change), 0) AS total_points FROM DRIVER_GREEN_POINT_LOG WHERE driver_id = ?');
if ($pointsStmt) {
    mysqli_stmt_bind_param($pointsStmt, 'i', $driverIdInt);
    mysqli_stmt_execute($pointsStmt);
    $pointsResult = mysqli_stmt_get_result($pointsStmt);
    if ($pointsResult && ($pointsRow = mysqli_fetch_assoc($pointsResult))) {
        $driverPoints = (int) ($pointsRow['total_points'] ?? 0);
    }
    if ($pointsResult) {
        mysqli_free_result($pointsResult);
    }
    mysqli_stmt_close($pointsStmt);
}

$redeemId = isset($_POST['redeem_id']) ? trim((string) $_POST['redeem_id']) : '';
if ($redeemId !== '') {
    $rewardIdInt = (int) $redeemId;
    if ($rewardIdInt <= 0) {
        echo "<script>alert('Invalid reward selected.');";
        die("window.location.href='redemption.php';</script>");
    }

    mysqli_begin_transaction($dbConn);
    $rollback = false;
    $errorMessage = '';

    $rewardStmt = mysqli_prepare($dbConn, 'SELECT reward_id, reward_name, points_required, stock FROM REWARD WHERE reward_id = ? LIMIT 1 FOR UPDATE');
    if ($rewardStmt) {
        mysqli_stmt_bind_param($rewardStmt, 'i', $rewardIdInt);
        mysqli_stmt_execute($rewardStmt);
        $rewardResult = mysqli_stmt_get_result($rewardStmt);
        $rewardRow = $rewardResult ? mysqli_fetch_assoc($rewardResult) : null;
        if ($rewardResult) {
            mysqli_free_result($rewardResult);
        }
        mysqli_stmt_close($rewardStmt);
    } else {
        $rewardRow = null;
    }

    if (!$rewardRow) {
        $rollback = true;
        $errorMessage = 'Reward not found.';
    } else {
        $stock = (int) ($rewardRow['stock'] ?? 0);
        $pointsRequired = (int) ($rewardRow['points_required'] ?? 0);
        $rewardName = (string) ($rewardRow['reward_name'] ?? '');

        if ($stock <= 0) {
            $rollback = true;
            $errorMessage = 'This reward is out of stock.';
        } else {
            $pointStmt = mysqli_prepare($dbConn, 'SELECT COALESCE(SUM(points_change), 0) AS total_points FROM DRIVER_GREEN_POINT_LOG WHERE driver_id = ? FOR UPDATE');
            $currentPoints = 0;
            if ($pointStmt) {
                mysqli_stmt_bind_param($pointStmt, 'i', $driverIdInt);
                mysqli_stmt_execute($pointStmt);
                $pointResult = mysqli_stmt_get_result($pointStmt);
                if ($pointResult && ($pointRow = mysqli_fetch_assoc($pointResult))) {
                    $currentPoints = (int) ($pointRow['total_points'] ?? 0);
                }
                if ($pointResult) {
                    mysqli_free_result($pointResult);
                }
                mysqli_stmt_close($pointStmt);
            }

            if ($currentPoints < $pointsRequired) {
                $rollback = true;
                $errorMessage = 'Not enough points to redeem this reward.';
            } else {
                $redeemStmt = mysqli_prepare($dbConn, 'INSERT INTO DRIVER_REDEMPTION (driver_id, reward_id) VALUES (?, ?)');
                if ($redeemStmt) {
                    mysqli_stmt_bind_param($redeemStmt, 'ii', $driverIdInt, $rewardIdInt);
                    mysqli_stmt_execute($redeemStmt);
                    mysqli_stmt_close($redeemStmt);
                } else {
                    $rollback = true;
                    $errorMessage = 'Unable to record redemption.';
                }

                if (!$rollback) {
                    $sourceLabel = 'Redeem: ' . $rewardName;
                    if (strlen($sourceLabel) > 50) {
                        $sourceLabel = substr($sourceLabel, 0, 50);
                    }

                    $logStmt = mysqli_prepare($dbConn, 'INSERT INTO DRIVER_GREEN_POINT_LOG (driver_id, points_change, source) VALUES (?, ?, ?)');
                    if ($logStmt) {
                        $negativePoints = -1 * $pointsRequired;
                        mysqli_stmt_bind_param($logStmt, 'iis', $driverIdInt, $negativePoints, $sourceLabel);
                        mysqli_stmt_execute($logStmt);
                        mysqli_stmt_close($logStmt);
                    } else {
                        $rollback = true;
                        $errorMessage = 'Unable to update points.';
                    }
                }

                if (!$rollback) {
                    $stockStmt = mysqli_prepare($dbConn, 'UPDATE REWARD SET stock = stock - 1 WHERE reward_id = ? AND stock > 0');
                    if ($stockStmt) {
                        mysqli_stmt_bind_param($stockStmt, 'i', $rewardIdInt);
                        mysqli_stmt_execute($stockStmt);
                        $affected = mysqli_stmt_affected_rows($stockStmt);
                        mysqli_stmt_close($stockStmt);
                        if ($affected <= 0) {
                            $rollback = true;
                            $errorMessage = 'Unable to update stock.';
                        }
                    } else {
                        $rollback = true;
                        $errorMessage = 'Unable to update stock.';
                    }
                }
            }
        }
    }

    if ($rollback) {
        mysqli_rollback($dbConn);
        $errorMessage = $errorMessage !== '' ? $errorMessage : 'Redemption failed.';
        echo "<script>alert('" . escapeHtml($errorMessage) . "');";
        die("window.location.href='redemption.php';</script>");
    }

    mysqli_commit($dbConn);
    echo "<script>alert('Redemption successful!');";
    die("window.location.href='redemption.php';</script>");
}

$rewardRows = [];
$rewardSql = "SELECT reward_id, reward_name, points_required, category, stock FROM REWARD WHERE stock > 0 ORDER BY points_required ASC, reward_name ASC";
$rewardResult = mysqli_query($dbConn, $rewardSql);
if ($rewardResult) {
    while ($row = mysqli_fetch_assoc($rewardResult)) {
        $rewardRows[] = $row;
    }
    mysqli_free_result($rewardResult);
}

$historyRows = [];
$historyStmt = mysqli_prepare($dbConn, 'SELECT r.reward_name, r.points_required, dr.redeemed_at FROM DRIVER_REDEMPTION dr JOIN REWARD r ON dr.reward_id = r.reward_id WHERE dr.driver_id = ? ORDER BY dr.redeemed_at DESC LIMIT 10');
if ($historyStmt) {
    mysqli_stmt_bind_param($historyStmt, 'i', $driverIdInt);
    mysqli_stmt_execute($historyStmt);
    $historyResult = mysqli_stmt_get_result($historyStmt);
    if ($historyResult) {
        while ($row = mysqli_fetch_assoc($historyResult)) {
            $historyRows[] = $row;
        }
        mysqli_free_result($historyResult);
    }
    mysqli_stmt_close($historyStmt);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoMove - Driver Redemption</title>
  <link rel="icon" type="image/svg+xml" href="../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="../../public-assets/style.css">
  <link rel="stylesheet" href="redemption.css">
</head>

<body>
  <nav class="mainNav">
    <div class="insideNav">
      <a href="dashboard.html" class="logo">CO<span>MOVE</span></a>
      <div class="navContents">
        <a href="dashboard.html" class="navContent"><img src="../../public-assets/icons/home.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true">
          Dashboard</a>
        <a href="my-rides.php" class="navContent"><img src="../../public-assets/icons/car.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> My Rides</a>
        <a href="earnings.html" class="navContent"><img src="../../public-assets/icons/dollar-sign.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Earnings</a>
        <a href="redemption.php" class="currentNav"><img src="../../public-assets/icons/gift.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Redemption</a>
        <a href="vehicle.html" class="navContent"><img src="../../public-assets/icons/file-text.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Vehicle</a>
        <a href="profile.html" class="navContent"><img src="../../public-assets/icons/user.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Profile</a>
      </div>
      <div class="nav-actions"><a href="../../../index.php" class="nav-logout" title="Log out"><img src="../../public-assets/icons/log-out.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"></a></div>
    </div>
  </nav>

  <main class="dashboardMain">
    <div class="pageHeader">
      <div class="pageTitleBlock">
        <h1 class="pageTitle">Reward <span>Redemption</span></h1>
        <p class="pageSubtitle">Use your points to claim driver perks and partner vouchers.</p>
      </div>
      <div class="pointsCard">
        <p class="pointsLabel">AVAILABLE POINTS</p>
        <p class="pointsValue"><?php echo number_format($driverPoints); ?></p>
        <p class="pointsHint">Earn more points by completing eco-friendly rides.</p>
      </div>
    </div>

    <section class="section">
      <div class="sectionHeader">
        <h2>Reward Catalogue</h2>
      </div>

      <div class="rewardTableWrap">
        <table class="rewardTable">
          <thead>
            <tr>
              <th>Reward</th>
              <th>Category</th>
              <th>Points</th>
              <th>Stock</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (count($rewardRows) === 0) {
                echo "<tr><td colspan='5'>No rewards available right now.</td></tr>";
            } else {
                foreach ($rewardRows as $row) {
                    $rewardName = (string) ($row['reward_name'] ?? '');
                    $pointsRequired = (int) ($row['points_required'] ?? 0);
                    $category = trim((string) ($row['category'] ?? ''));
                    $stock = (int) ($row['stock'] ?? 0);
                    $canRedeem = $driverPoints >= $pointsRequired;
                    $buttonLabel = $canRedeem ? 'Redeem' : 'Not enough points';
                    $buttonDisabled = $canRedeem ? '' : ' disabled';
                    $categoryLabel = $category === '' ? 'General' : $category;
                    ?>
                    <tr>
                      <td data-label="Reward">
                        <div class="rewardName"><?php echo escapeHtml($rewardName); ?></div>
                        <div class="rewardDesc">Redeem with your points.</div>
                      </td>
                      <td data-label="Category"><span class="rewardBadge"><?php echo escapeHtml($categoryLabel); ?></span></td>
                      <td data-label="Points" class="mono"><?php echo number_format($pointsRequired); ?></td>
                      <td data-label="Stock" class="stock mono"><?php echo $stock; ?></td>
                      <td data-label="Action">
                        <form method="post" action="redemption.php">
                          <input type="hidden" name="redeem_id" value="<?php echo (int) $row['reward_id']; ?>">
                          <button class="btn"<?php echo $buttonDisabled; ?>><?php echo $buttonLabel; ?></button>
                        </form>
                      </td>
                    </tr>
                    <?php
                }
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="section">
      <div class="sectionHeader">
        <h2>Recent Redemptions</h2>
      </div>
      <div class="historyTableWrap">
        <table class="historyTable">
          <thead>
            <tr>
              <th>Reward</th>
              <th>Points</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (count($historyRows) === 0) {
                echo "<tr><td colspan='3'>No redemptions yet.</td></tr>";
            } else {
                foreach ($historyRows as $row) {
                    $rewardName = (string) ($row['reward_name'] ?? '');
                    $pointsRequired = (int) ($row['points_required'] ?? 0);
                    $redeemedAt = (string) ($row['redeemed_at'] ?? '');
                    $dateLabel = $redeemedAt !== '' ? date('M d, Y', strtotime($redeemedAt)) : '-';
                    echo "<tr>";
                    echo "<td data-label='Reward'>" . escapeHtml($rewardName) . "</td>";
                    echo "<td data-label='Points' class='mono'>" . number_format($pointsRequired) . "</td>";
                    echo "<td data-label='Date' class='mono'>" . escapeHtml($dateLabel) . "</td>";
                    echo "</tr>";
                }
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <nav class="bottom-nav driver-bottom-nav">
    <a href="dashboard.html"><img src="../../public-assets/icons/home.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="my-rides.php"><img src="../../public-assets/icons/car.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="earnings.html"><img src="../../public-assets/icons/dollar-sign.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="redemption.php" class="active"><img src="../../public-assets/icons/gift.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="vehicle.html"><img src="../../public-assets/icons/file-text.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="profile.html"><img src="../../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
  </nav>

  <script src="../../public-assets/script.js"></script>
  <script src="access-guard.js"></script>
</body>

</html>
