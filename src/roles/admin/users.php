<?php
$pageTitle = 'Users';
$activePage = 'users';
require_once __DIR__ . '/includes/header.php';

$userMessage = '';
$userError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_user_status'])) {
    $targetRole = strtolower(trim((string) ($_POST['target_role'] ?? '')));
    $targetId = (int) ($_POST['target_id'] ?? 0);
    $targetStatus = strtolower(trim((string) ($_POST['target_status'] ?? '')));

    if ($targetId > 0 && in_array($targetStatus, ['active', 'banned'], true)) {
        if ($targetRole === 'rider') {
            $stmt = mysqli_prepare($dbConn, 'UPDATE RIDER SET rider_status = ? WHERE rider_id = ?');
        } elseif ($targetRole === 'driver') {
            $stmt = mysqli_prepare($dbConn, 'UPDATE DRIVER SET driver_status = ? WHERE driver_id = ?');
        } else {
            $stmt = false;
        }

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'si', $targetStatus, $targetId);
            if (mysqli_stmt_execute($stmt)) {
                $userMessage = 'User status updated to ' . ucfirst($targetStatus) . '.';
            } else {
                $userError = 'Unable to update user status.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $userError = 'Invalid user status request.';
        }
    } else {
        $userError = 'Invalid user status request.';
    }
}

// Live admin queries for user management tables.
$allUsers = adminFetchAll(
    $dbConn,
    "SELECT rider_id AS id, name, email, phone_number AS phone, 'Rider' AS role, rider_status AS status, profile_photo
     FROM RIDER
     UNION ALL
     SELECT driver_id AS id, name, email, phone_number AS phone, 'Driver' AS role, driver_status AS status, profile_photo
     FROM DRIVER
     ORDER BY name ASC"
);

$riders = adminFetchAll(
    $dbConn,
    "SELECT
        r.rider_id,
        r.name,
        r.email,
        r.profile_photo,
        r.rider_status,
        COALESCE(SUM(rr.amount_paid), 0) AS wallet_amount,
        COALESCE(SUM(rg.points_change), 0) AS green_points,
        COUNT(DISTINCT rr.request_id) AS trip_count
     FROM RIDER r
     LEFT JOIN RIDE_REQUEST rr ON rr.rider_id = r.rider_id AND rr.request_status = 'approved'
     LEFT JOIN RIDER_GREEN_POINT_LOG rg ON rg.rider_id = r.rider_id
     GROUP BY r.rider_id, r.name, r.email, r.profile_photo, r.rider_status
     ORDER BY r.name ASC"
);

$drivers = adminFetchAll(
    $dbConn,
    "SELECT
        d.driver_id,
        d.name,
        d.email,
        d.profile_photo,
        d.driver_status,
        d.plate_number,
        d.vehicle_model,
        d.license_expiry_date,
        ROUND(AVG(rt.rating_score), 1) AS rating_value
     FROM DRIVER d
     LEFT JOIN TRIP t ON t.driver_id = d.driver_id
     LEFT JOIN RATING rt ON rt.trip_id = t.trip_id
     GROUP BY d.driver_id, d.name, d.email, d.profile_photo, d.driver_status, d.plate_number, d.vehicle_model, d.license_expiry_date
     ORDER BY d.name ASC"
);

$pendingDrivers = adminFetchAll(
    $dbConn,
    "SELECT driver_id, name, email, profile_photo, plate_number, vehicle_model, created_at, driver_status
     FROM DRIVER
     WHERE driver_status = ?
     ORDER BY created_at DESC",
    's',
    ['pending']
);
?>
  <main class="dashboard-main">
    <h1 class="page-title">User Management</h1>
    <p class="page-subtitle">Create, edit, manage and monitor all platform users with live database data.</p>

    <?php if ($userMessage !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(200,241,53,0.2)"><?php echo adminEscape($userMessage); ?></div>
    <?php } ?>
    <?php if ($userError !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(255,71,87,0.3)"><?php echo adminEscape($userError); ?></div>
    <?php } ?>
    <div class="sub-tabs">
      <button class="sub-tab active" id="utab-all" onclick="switchTab('utab-','usersub-',['all','riders','drivers','pending'],'all')">All Users</button>
      <button class="sub-tab" id="utab-riders" onclick="switchTab('utab-','usersub-',['all','riders','drivers','pending'],'riders')">Riders</button>
      <button class="sub-tab" id="utab-drivers" onclick="switchTab('utab-','usersub-',['all','riders','drivers','pending'],'drivers')">Drivers</button>
      <button class="sub-tab" id="utab-pending" onclick="switchTab('utab-','usersub-',['all','riders','drivers','pending'],'pending')">Driver Applications <span class="badge b-yellow" style="margin-left:4px"><?php echo adminEscape(count($pendingDrivers)); ?></span></button>
    </div>

    <div id="usersub-all">
      <div class="card">
        <div class="card-header">
          <span class="card-title">All Users</span>
          <div class="card-actions">
            <input class="search-input" placeholder="Search users..." oninput="filterTable('allUsersTable', this.value)">
          </div>
        </div>
        <div class="table-wrap">
          <table id="allUsersTable">
            <thead><tr><th>User</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Action</th></tr></thead>
            <tbody id="allUsersBody">
              <?php foreach ($allUsers as $user) { ?>
                <tr data-uid="<?php echo adminEscape($user['role'] . '-' . $user['id']); ?>">
                  <td><div class="user-cell"><img class="user-avatar" src="<?php echo adminEscape(adminAvatarSrc($user['profile_photo'])); ?>" alt="User avatar" onerror="this.src='../../public-assets/images/profile-icon.png'"><div><div class="user-name"><?php echo adminEscape($user['name']); ?></div><div class="user-sub">#<?php echo adminEscape(strtoupper(substr($user['role'], 0, 1)) . '-' . str_pad((string) $user['id'], 4, '0', STR_PAD_LEFT)); ?></div></div></div></td>
                  <td><?php echo adminEscape($user['email']); ?></td>
                  <td><?php echo adminEscape($user['phone']); ?></td>
                  <td><span class="badge <?php echo adminEscape(adminRoleBadgeClass($user['role'])); ?>"><?php echo adminEscape($user['role']); ?></span></td>
                  <td><span class="badge <?php echo adminEscape(adminStatusBadgeClass($user['status'])); ?>"><span class="bdot"></span> <?php echo adminEscape(ucfirst($user['status'])); ?></span></td>
                  <td>
                    <form method="post" class="action-btns">
                      <input type="hidden" name="change_user_status" value="1">
                      <input type="hidden" name="target_role" value="<?php echo adminEscape(strtolower($user['role'])); ?>">
                      <input type="hidden" name="target_id" value="<?php echo adminEscape($user['id']); ?>">
                      <input type="hidden" name="target_status" value="active">
                      <button class="btn btn-xs btn-lime" type="submit">Active</button>
                    </form>
                    <form method="post" class="action-btns" style="margin-top:4px">
                      <input type="hidden" name="change_user_status" value="1">
                      <input type="hidden" name="target_role" value="<?php echo adminEscape(strtolower($user['role'])); ?>">
                      <input type="hidden" name="target_id" value="<?php echo adminEscape($user['id']); ?>">
                      <input type="hidden" name="target_status" value="banned">
                      <button class="btn btn-xs btn-danger" type="submit">Ban</button>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="usersub-riders" style="display:none">
      <div class="card">
        <div class="card-header"><span class="card-title">Riders</span><div class="card-actions"><input class="search-input" placeholder="Search..." oninput="filterTable('ridersTable',this.value)"></div></div>
        <div class="table-wrap">
          <table id="ridersTable">
            <thead><tr><th>Rider</th><th>Wallet</th><th>Green Points</th><th>Trips</th><th>Status</th><th>Set Status</th></tr></thead>
            <tbody>
              <?php foreach ($riders as $rider) { ?>
                <tr>
                  <td><div class="user-cell"><img class="user-avatar" src="<?php echo adminEscape(adminAvatarSrc($rider['profile_photo'])); ?>" alt="Rider avatar" onerror="this.src='../../public-assets/images/profile-icon.png'"><div><div class="user-name"><?php echo adminEscape($rider['name']); ?></div><div class="user-sub">#R-<?php echo adminEscape(str_pad((string) $rider['rider_id'], 4, '0', STR_PAD_LEFT)); ?></div></div></div></td>
                  <td style="font-family:'DM Mono',monospace">RM <?php echo adminEscape(number_format((float) $rider['wallet_amount'], 2)); ?></td>
                  <td><span class="badge b-lime"><?php echo adminEscape(number_format((int) $rider['green_points'])); ?> pts</span></td>
                  <td><?php echo adminEscape((int) $rider['trip_count']); ?></td>
                  <td><span class="badge <?php echo adminEscape(adminStatusBadgeClass($rider['rider_status'])); ?>"><span class="bdot"></span> <?php echo adminEscape(ucfirst($rider['rider_status'])); ?></span></td>
                  <td><div class="action-btns"><form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="rider"><input type="hidden" name="target_id" value="<?php echo adminEscape($rider['rider_id']); ?>"><input type="hidden" name="target_status" value="active"><button class="btn btn-xs btn-lime" type="submit">Active</button></form><form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="rider"><input type="hidden" name="target_id" value="<?php echo adminEscape($rider['rider_id']); ?>"><input type="hidden" name="target_status" value="banned"><button class="btn btn-xs btn-danger" type="submit">Ban</button></form></div></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="usersub-drivers" style="display:none">
      <div class="card">
        <div class="card-header"><span class="card-title">Drivers</span><div class="card-actions"><input class="search-input" placeholder="Search..." oninput="filterTable('driversTable',this.value)"></div></div>
        <div class="table-wrap">
          <table id="driversTable">
            <thead><tr><th>Driver</th><th>Plate</th><th>Vehicle</th><th>License Expiry</th><th>Rating</th><th>Status</th><th>Set Status</th></tr></thead>
            <tbody>
              <?php foreach ($drivers as $driver) { ?>
                <tr>
                  <td><div class="user-cell"><img class="user-avatar" src="<?php echo adminEscape(adminAvatarSrc($driver['profile_photo'])); ?>" alt="Driver avatar" onerror="this.src='../../public-assets/images/profile-icon.png'"><div><div class="user-name"><?php echo adminEscape($driver['name']); ?></div><div class="user-sub">#D-<?php echo adminEscape(str_pad((string) $driver['driver_id'], 4, '0', STR_PAD_LEFT)); ?></div></div></div></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($driver['plate_number']); ?></td>
                  <td style="font-size:12px"><?php echo adminEscape($driver['vehicle_model']); ?></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($driver['license_expiry_date']); ?></td>
                  <td><span class="stars"><?php echo adminEscape($driver['rating_value'] !== null ? number_format((float) $driver['rating_value'], 1) . '/5.0' : 'No ratings'); ?></span></td>
                  <td><span class="badge <?php echo adminEscape(adminStatusBadgeClass($driver['driver_status'])); ?>"><span class="bdot"></span> <?php echo adminEscape(ucfirst($driver['driver_status'])); ?></span></td>
                  <td><div class="action-btns"><form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($driver['driver_id']); ?>"><input type="hidden" name="target_status" value="active"><button class="btn btn-xs btn-lime" type="submit">Active</button></form><form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($driver['driver_id']); ?>"><input type="hidden" name="target_status" value="banned"><button class="btn btn-xs btn-danger" type="submit">Ban</button></form></div></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="usersub-pending" style="display:none">
      <div class="card">
        <div class="card-header"><span class="card-title">Driver Applications</span><span class="badge b-yellow"><?php echo adminEscape(count($pendingDrivers)); ?> Pending</span></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Applicant</th><th>Email</th><th>Plate</th><th>Vehicle</th><th>Submitted</th><th>Status</th><th>Set Status</th></tr></thead>
            <tbody>
              <?php foreach ($pendingDrivers as $pendingDriver) { ?>
                <tr>
                  <td><div class="user-cell"><img class="user-avatar" src="<?php echo adminEscape(adminAvatarSrc($pendingDriver['profile_photo'])); ?>" alt="Applicant avatar" onerror="this.src='../../public-assets/images/profile-icon.png'"><div><div class="user-name"><?php echo adminEscape($pendingDriver['name']); ?></div><div class="user-sub">#D-<?php echo adminEscape(str_pad((string) $pendingDriver['driver_id'], 4, '0', STR_PAD_LEFT)); ?></div></div></div></td>
                  <td><?php echo adminEscape($pendingDriver['email']); ?></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($pendingDriver['plate_number']); ?></td>
                  <td style="font-size:12px"><?php echo adminEscape($pendingDriver['vehicle_model']); ?></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($pendingDriver['created_at']); ?></td>
                  <td><span class="badge <?php echo adminEscape(adminStatusBadgeClass($pendingDriver['driver_status'])); ?>"><span class="bdot"></span> <?php echo adminEscape(ucfirst($pendingDriver['driver_status'])); ?></span></td>
                  <td><div class="action-btns"><form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($pendingDriver['driver_id']); ?>"><input type="hidden" name="target_status" value="active"><button class="btn btn-xs btn-lime" type="submit">Active</button></form><form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($pendingDriver['driver_id']); ?>"><input type="hidden" name="target_status" value="banned"><button class="btn btn-xs btn-danger" type="submit">Ban</button></form></div></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
