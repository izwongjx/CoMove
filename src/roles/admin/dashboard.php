<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header.php';

// Dashboard queries are kept inside the admin module and read from the existing schema.
$totalUsersRow = adminFetchOne($dbConn, 'SELECT (SELECT COUNT(*) FROM RIDER) + (SELECT COUNT(*) FROM DRIVER) AS total_users');
$totalTripsRow = adminFetchOne($dbConn, 'SELECT COUNT(*) AS total_trips FROM TRIP');
$totalPointsRow = adminFetchOne($dbConn, 'SELECT COALESCE((SELECT SUM(points_change) FROM RIDER_GREEN_POINT_LOG), 0) + COALESCE((SELECT SUM(points_change) FROM DRIVER_GREEN_POINT_LOG), 0) AS total_points');
$pendingDriversRow = adminFetchOne($dbConn, 'SELECT COUNT(*) AS pending_drivers FROM DRIVER WHERE driver_status = ?', 's', ['pending']);

$activeDrivers = (int) (adminFetchOne($dbConn, 'SELECT COUNT(*) AS total FROM DRIVER WHERE driver_status = ?', 's', ['active'])['total'] ?? 0);
$totalDrivers = (int) (adminFetchOne($dbConn, 'SELECT COUNT(*) AS total FROM DRIVER')['total'] ?? 0);
$completionRate = (int) (adminFetchOne($dbConn, "SELECT ROUND((SUM(CASE WHEN trip_status = 'completed' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) * 100) AS rate FROM TRIP")['rate'] ?? 0);
$rewardRedeemed = (int) (adminFetchOne($dbConn, 'SELECT (SELECT COUNT(*) FROM RIDER_REDEMPTION) + (SELECT COUNT(*) FROM DRIVER_REDEMPTION) AS total')['total'] ?? 0);
$rewardTotal = (int) (adminFetchOne($dbConn, 'SELECT COALESCE(SUM(stock), 0) AS total FROM REWARD')['total'] ?? 0);

$recentDrivers = adminFetchAll($dbConn, 'SELECT name, created_at FROM DRIVER ORDER BY created_at DESC LIMIT 3');
$recentTrips = adminFetchAll($dbConn, 'SELECT trip_id, total_amount, departure_time, trip_status FROM TRIP ORDER BY departure_time DESC LIMIT 3');

$tripDays = ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0];
$tripStats = adminFetchAll(
    $dbConn,
    "SELECT DATE_FORMAT(departure_time, '%a') AS trip_day, COUNT(*) AS trip_total
     FROM TRIP
     WHERE departure_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     GROUP BY DATE_FORMAT(departure_time, '%a')"
);
foreach ($tripStats as $tripStat) {
    if (isset($tripDays[$tripStat['trip_day']])) {
        $tripDays[$tripStat['trip_day']] = (int) $tripStat['trip_total'];
    }
}
$maxTrips = max($tripDays);
if ($maxTrips < 1) {
    $maxTrips = 1;
}
?>
  <main class="dashboard-main">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">System overview powered by live database data</p>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-card-accent" style="background:var(--lime)"></div><div class="stat-icon">Users</div><div class="stat-value" style="color:var(--lime)"><?php echo adminEscape((int) ($totalUsersRow['total_users'] ?? 0)); ?></div><div class="stat-label">Total Users</div></div>
      <div class="stat-card"><div class="stat-card-accent" style="background:var(--info)"></div><div class="stat-icon">Trips</div><div class="stat-value" style="color:var(--info)"><?php echo adminEscape((int) ($totalTripsRow['total_trips'] ?? 0)); ?></div><div class="stat-label">Total Trips</div></div>
      <div class="stat-card"><div class="stat-card-accent" style="background:var(--purple)"></div><div class="stat-icon">Points</div><div class="stat-value" style="color:var(--purple)"><?php echo adminEscape(number_format((int) ($totalPointsRow['total_points'] ?? 0))); ?></div><div class="stat-label">Green Points</div></div>
      <div class="stat-card"><div class="stat-card-accent" style="background:var(--warn)"></div><div class="stat-icon">Pending</div><div class="stat-value" style="color:var(--warn)"><?php echo adminEscape((int) ($pendingDriversRow['pending_drivers'] ?? 0)); ?></div><div class="stat-label">Pending Drivers</div></div>
    </div>

    <div class="chart-row">
      <div class="card">
        <div class="card-header"><span class="card-title">Trip Volume - Last 7 Days</span></div>
        <div style="padding:18px 18px 12px">
          <div class="mini-chart">
            <?php foreach ($tripDays as $label => $value) { ?>
              <div class="bar" style="height:<?php echo adminEscape((string) max(6, (int) round(($value / $maxTrips) * 100))); ?>%;background:<?php echo $value === $maxTrips ? 'var(--lime)' : 'rgba(200,241,53,0.38)'; ?>" title="<?php echo adminEscape($label . ': ' . $value . ' trips'); ?>"></div>
            <?php } ?>
          </div>
          <div class="chart-labels">
            <?php foreach (array_keys($tripDays) as $label) { ?>
              <span><?php echo adminEscape($label); ?></span>
            <?php } ?>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><span class="card-title">Platform Health</span></div>
        <div style="padding:14px 18px;display:flex;flex-direction:column;gap:13px">
          <div><div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;margin-bottom:4px"><span>Active Drivers</span><span style="color:var(--lime);font-family:'DM Mono',monospace"><?php echo adminEscape($activeDrivers . '/' . max(1, $totalDrivers)); ?></span></div><div class="progress-bar"><div class="progress-fill" style="width:<?php echo adminEscape((string) ($totalDrivers > 0 ? (int) round(($activeDrivers / $totalDrivers) * 100) : 0)); ?>%;background:var(--lime)"></div></div></div>
          <div><div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;margin-bottom:4px"><span>Trip Completion</span><span style="color:var(--info);font-family:'DM Mono',monospace"><?php echo adminEscape($completionRate); ?>%</span></div><div class="progress-bar"><div class="progress-fill" style="width:<?php echo adminEscape((string) $completionRate); ?>%;background:var(--info)"></div></div></div>
          <div><div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;margin-bottom:4px"><span>Rewards Redeemed</span><span style="color:var(--purple);font-family:'DM Mono',monospace"><?php echo adminEscape($rewardRedeemed . '/' . max(1, $rewardTotal)); ?></span></div><div class="progress-bar"><div class="progress-fill" style="width:<?php echo adminEscape((string) ($rewardTotal > 0 ? min(100, (int) round(($rewardRedeemed / $rewardTotal) * 100)) : 0)); ?>%;background:var(--purple)"></div></div></div>
          <div><div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;margin-bottom:4px"><span>Pending Drivers</span><span style="color:var(--warn);font-family:'DM Mono',monospace"><?php echo adminEscape((int) ($pendingDriversRow['pending_drivers'] ?? 0)); ?></span></div><div class="progress-bar"><div class="progress-fill" style="width:<?php echo adminEscape((string) min(100, ((int) ($pendingDriversRow['pending_drivers'] ?? 0)) * 10)); ?>%;background:var(--warn)"></div></div></div>
        </div>
      </div>
    </div>

    <div class="section-block">
      <div class="section-title">Recent Activity</div>
      <div class="card">
        <?php foreach ($recentDrivers as $driver) { ?>
          <div class="activity-item"><div class="activity-dot" style="background:var(--lime)"></div><div><div class="activity-text">New driver <strong><?php echo adminEscape($driver['name']); ?></strong> registered in the system</div><div class="activity-time"><?php echo adminEscape($driver['created_at']); ?></div></div></div>
        <?php } ?>
        <?php foreach ($recentTrips as $trip) { ?>
          <div class="activity-item"><div class="activity-dot" style="background:var(--info)"></div><div><div class="activity-text">Trip <strong>#TRP-<?php echo adminEscape($trip['trip_id']); ?></strong> recorded with status <strong><?php echo adminEscape($trip['trip_status']); ?></strong> and fare RM <?php echo adminEscape(number_format((float) $trip['total_amount'], 2)); ?></div><div class="activity-time"><?php echo adminEscape($trip['departure_time']); ?></div></div></div>
        <?php } ?>
      </div>
    </div>
  </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
