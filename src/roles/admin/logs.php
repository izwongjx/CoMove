<?php
$pageTitle = 'Logs';
$activePage = 'logs';
require_once __DIR__ . '/includes/header.php';

$tripLogs = adminFetchAll(
    $dbConn,
    "SELECT
        t.trip_id,
        d.name AS driver_name,
        COALESCE(GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', '), 'No riders') AS rider_names,
        t.total_amount,
        t.estimated_duration,
        t.gained_point
     FROM TRIP t
     INNER JOIN DRIVER d ON d.driver_id = t.driver_id
     LEFT JOIN RIDE_REQUEST rq ON rq.trip_id = t.trip_id AND rq.request_status = 'approved'
     LEFT JOIN RIDER r ON r.rider_id = rq.rider_id
     GROUP BY t.trip_id, d.name, t.total_amount, t.estimated_duration, t.gained_point
     ORDER BY t.departure_time DESC"
);

$requestLogs = adminFetchAll(
    $dbConn,
    "SELECT
        rq.request_id,
        r.name AS rider_name,
        t.start_location,
        t.end_location,
        rq.requested_at
     FROM RIDE_REQUEST rq
     INNER JOIN RIDER r ON r.rider_id = rq.rider_id
     INNER JOIN TRIP t ON t.trip_id = rq.trip_id
     ORDER BY rq.requested_at DESC"
);

$greenPointLogs = adminFetchAll(
    $dbConn,
    "SELECT log_id, user_name, source, points_change, created_at, role FROM (
        SELECT rg.log_id, r.name AS user_name, rg.source, rg.points_change, rg.created_at, 'Rider' AS role
        FROM RIDER_GREEN_POINT_LOG rg
        INNER JOIN RIDER r ON r.rider_id = rg.rider_id
        UNION ALL
        SELECT dg.log_id, d.name AS user_name, dg.source, dg.points_change, dg.created_at, 'Driver' AS role
        FROM DRIVER_GREEN_POINT_LOG dg
        INNER JOIN DRIVER d ON d.driver_id = dg.driver_id
     ) gp
     ORDER BY created_at DESC"
);

$issuedPoints = (int) (adminFetchOne($dbConn, 'SELECT COALESCE((SELECT SUM(points_change) FROM RIDER_GREEN_POINT_LOG), 0) + COALESCE((SELECT SUM(points_change) FROM DRIVER_GREEN_POINT_LOG), 0) AS total')['total'] ?? 0);
$redeemedPoints = (int) (adminFetchOne(
    $dbConn,
    'SELECT COALESCE((SELECT SUM(r.points_required) FROM RIDER_REDEMPTION rr INNER JOIN REWARD r ON r.reward_id = rr.reward_id), 0) + COALESCE((SELECT SUM(r.points_required) FROM DRIVER_REDEMPTION dr INNER JOIN REWARD r ON r.reward_id = dr.reward_id), 0) AS total'
)['total'] ?? 0);
$circulationPoints = $issuedPoints - $redeemedPoints;
?>
  <main class="dashboard-main">
    <h1 class="page-title">Logs</h1>
    <p class="page-subtitle">Trips, ride requests and green point activity from live database records.</p>

    <div class="sub-tabs">
      <button class="sub-tab active" id="ltab-trips" onclick="switchTab('ltab-','logsub-',['trips','requests','gpoints'],'trips')">Trips</button>
      <button class="sub-tab" id="ltab-requests" onclick="switchTab('ltab-','logsub-',['trips','requests','gpoints'],'requests')">Ride Requests</button>
      <button class="sub-tab" id="ltab-gpoints" onclick="switchTab('ltab-','logsub-',['trips','requests','gpoints'],'gpoints')">Green Points</button>
    </div>

    <div id="logsub-trips">
      <div class="card">
        <div class="card-header"><span class="card-title">Trip Log</span><div class="card-actions"><input class="search-input" placeholder="Search..." oninput="filterTable('tripsTable',this.value)"></div></div>
        <div class="table-wrap">
          <table id="tripsTable">
            <thead><tr><th>Trip ID</th><th>Driver</th><th>Rider</th><th>Fare</th><th>Duration</th><th>Green Pts</th></tr></thead>
            <tbody>
              <?php foreach ($tripLogs as $trip) { ?>
                <tr>
                  <td style="font-family:'DM Mono',monospace">#TRP-<?php echo adminEscape(str_pad((string) $trip['trip_id'], 4, '0', STR_PAD_LEFT)); ?></td>
                  <td><?php echo adminEscape($trip['driver_name']); ?></td>
                  <td><?php echo adminEscape($trip['rider_names']); ?></td>
                  <td style="font-family:'DM Mono',monospace">RM <?php echo adminEscape(number_format((float) $trip['total_amount'], 2)); ?></td>
                  <td><?php echo adminEscape((int) $trip['estimated_duration']); ?> min</td>
                  <td><span class="badge b-lime">+<?php echo adminEscape((int) $trip['gained_point']); ?></span></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="logsub-requests" style="display:none">
      <div class="card">
        <div class="card-header"><span class="card-title">Ride Requests</span><div class="card-actions"><input class="search-input" placeholder="Search..." oninput="filterTable('requestsTable',this.value)"></div></div>
        <div class="table-wrap">
          <table id="requestsTable">
            <thead><tr><th>Request ID</th><th>Rider</th><th>Pickup</th><th>Dropoff</th><th>Time</th></tr></thead>
            <tbody>
              <?php foreach ($requestLogs as $request) { ?>
                <tr>
                  <td style="font-family:'DM Mono',monospace">#REQ-<?php echo adminEscape(str_pad((string) $request['request_id'], 4, '0', STR_PAD_LEFT)); ?></td>
                  <td><?php echo adminEscape($request['rider_name']); ?></td>
                  <td><?php echo adminEscape($request['start_location']); ?></td>
                  <td><?php echo adminEscape($request['end_location']); ?></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($request['requested_at']); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="logsub-gpoints" style="display:none">
      <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:16px">
        <div class="stat-card"><div class="stat-card-accent" style="background:var(--lime)"></div><div class="stat-icon">Points</div><div class="stat-value" style="color:var(--lime)"><?php echo adminEscape(number_format($issuedPoints)); ?></div><div class="stat-label">Total Issued</div></div>
        <div class="stat-card"><div class="stat-card-accent" style="background:var(--info)"></div><div class="stat-icon">Redeemed</div><div class="stat-value" style="color:var(--info)"><?php echo adminEscape(number_format($redeemedPoints)); ?></div><div class="stat-label">Redeemed</div></div>
        <div class="stat-card"><div class="stat-card-accent" style="background:var(--purple)"></div><div class="stat-icon">Balance</div><div class="stat-value" style="color:var(--purple)"><?php echo adminEscape(number_format($circulationPoints)); ?></div><div class="stat-label">In Circulation</div></div>
      </div>
      <div class="card">
        <div class="card-header"><span class="card-title">Green Point Log</span><div class="card-actions"><input class="search-input" placeholder="Filter..." oninput="filterTable('gpTable',this.value)"></div></div>
        <div class="table-wrap">
          <table id="gpTable">
            <thead><tr><th>Log ID</th><th>User</th><th>Role</th><th>Source</th><th>Points</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($greenPointLogs as $log) { ?>
                <tr>
                  <td style="font-family:'DM Mono',monospace">#GP-<?php echo adminEscape(str_pad((string) $log['log_id'], 4, '0', STR_PAD_LEFT)); ?></td>
                  <td><?php echo adminEscape($log['user_name']); ?></td>
                  <td><span class="badge <?php echo adminEscape(adminRoleBadgeClass($log['role'])); ?>"><?php echo adminEscape($log['role']); ?></span></td>
                  <td><?php echo adminEscape($log['source']); ?></td>
                  <td><span class="badge b-lime"><?php echo adminEscape(((int) $log['points_change'] >= 0 ? '+' : '') . (int) $log['points_change']); ?></span></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($log['created_at']); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
