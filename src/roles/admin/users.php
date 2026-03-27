<?php
$pageTitle = 'Users';
$activePage = 'users';
require_once __DIR__ . '/includes/header.php';

$userMessage = '';
$userError = '';

function adminUserRedirect(string $query = ''): void
{
    header('Location: users.php' . $query);
    exit;
}

function adminUserAllowedStatuses(string $role): array
{
    return $role === 'driver' ? ['pending', 'active', 'banned', 'rejected'] : ['active', 'banned'];
}

function adminDeleteRider(mysqli $dbConn, int $riderId): bool
{
    mysqli_begin_transaction($dbConn);

    try {
        $steps = [
            ['DELETE FROM RATING WHERE rider_id = ?', 'i', [$riderId]],
            ['DELETE FROM RIDER_REDEMPTION WHERE rider_id = ?', 'i', [$riderId]],
            ['DELETE FROM RIDER_GREEN_POINT_LOG WHERE rider_id = ?', 'i', [$riderId]],
            ['DELETE FROM RIDER_SOCIAL_LINK WHERE rider_id = ?', 'i', [$riderId]],
            ['DELETE FROM RIDER_FRIEND WHERE rider_id = ? OR friend_rider_id = ?', 'ii', [$riderId, $riderId]],
            ['DELETE FROM TRIP_SHARE WHERE rider_id = ?', 'i', [$riderId]],
            ['DELETE FROM RIDE_REQUEST WHERE rider_id = ?', 'i', [$riderId]],
            ['DELETE FROM RIDER WHERE rider_id = ?', 'i', [$riderId]],
        ];

        foreach ($steps as [$sql, $types, $params]) {
            if (!adminExecuteStatement($dbConn, $sql, $types, $params)) {
                throw new RuntimeException('Failed to delete rider data.');
            }
        }

        mysqli_commit($dbConn);
        return true;
    } catch (Throwable $exception) {
        mysqli_rollback($dbConn);
        return false;
    }
}

function adminDeleteDriver(mysqli $dbConn, int $driverId): bool
{
    mysqli_begin_transaction($dbConn);

    try {
        $tripScopedSteps = [
            'DELETE FROM RATING WHERE trip_id IN (SELECT trip_id FROM (SELECT trip_id FROM TRIP WHERE driver_id = ?) AS trip_scope)',
            'DELETE FROM TRIP_SHARE WHERE trip_id IN (SELECT trip_id FROM (SELECT trip_id FROM TRIP WHERE driver_id = ?) AS trip_scope)',
            'DELETE FROM RIDE_REQUEST WHERE trip_id IN (SELECT trip_id FROM (SELECT trip_id FROM TRIP WHERE driver_id = ?) AS trip_scope)',
        ];

        foreach ($tripScopedSteps as $sql) {
            if (!adminExecuteStatement($dbConn, $sql, 'i', [$driverId])) {
                throw new RuntimeException('Failed to delete trip-linked driver data.');
            }
        }

        $steps = [
            ['DELETE FROM DRIVER_REDEMPTION WHERE driver_id = ?', 'i', [$driverId]],
            ['DELETE FROM DRIVER_GREEN_POINT_LOG WHERE driver_id = ?', 'i', [$driverId]],
            ['DELETE FROM TRIP WHERE driver_id = ?', 'i', [$driverId]],
            ['DELETE FROM DRIVER WHERE driver_id = ?', 'i', [$driverId]],
        ];

        foreach ($steps as [$sql, $types, $params]) {
            if (!adminExecuteStatement($dbConn, $sql, $types, $params)) {
                throw new RuntimeException('Failed to delete driver data.');
            }
        }

        mysqli_commit($dbConn);
        return true;
    } catch (Throwable $exception) {
        mysqli_rollback($dbConn);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_user'])) {
        $role = strtolower(trim((string) ($_POST['role'] ?? 'rider')));
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordHash = $password !== '' ? md5($password) : '';
        $status = strtolower(trim((string) ($_POST['status'] ?? ($role === 'driver' ? 'pending' : 'active'))));

        if ($name === '' || $email === '' || $phone === '' || $password === '') {
            $userError = 'Name, email, phone and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $userError = 'Please enter a valid email address.';
        } elseif (!in_array($status, adminUserAllowedStatuses($role), true)) {
            $userError = 'Invalid user status selected.';
        } elseif ($role === 'rider') {
            $stmt = mysqli_prepare($dbConn, 'INSERT INTO RIDER (name, email, password, phone_number, rider_status) VALUES (?, ?, ?, ?, ?)');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $passwordHash, $phone, $status);
                $userMessage = mysqli_stmt_execute($stmt) ? 'Rider created successfully.' : 'Unable to create rider. Please check for duplicate email values.';
                mysqli_stmt_close($stmt);
            } else {
                $userError = 'Unable to prepare rider creation.';
            }
            if ($userMessage === '') {
                $userError = $userError !== '' ? $userError : 'Unable to create rider. Please check for duplicate email values.';
            }
        } elseif ($role === 'driver') {
            $nricNumber = trim((string) ($_POST['nric_number'] ?? ''));
            $licenseExpiryDate = trim((string) ($_POST['license_expiry_date'] ?? ''));
            $vehicleModel = trim((string) ($_POST['vehicle_model'] ?? ''));
            $plateNumber = strtoupper(trim((string) ($_POST['plate_number'] ?? '')));
            $color = trim((string) ($_POST['color'] ?? ''));
            $approvedBy = adminCurrentAdminId();
            $placeholderImage = adminLoadAssetBlob('../../public-assets/images/profile-icon.png');

            if ($nricNumber === '' || $licenseExpiryDate === '' || $plateNumber === '') {
                $userError = 'Driver NRIC, license expiry date and plate number are required.';
            } elseif ($placeholderImage === null) {
                $userError = 'Default document image is missing, so driver creation cannot complete.';
            } else {
                $stmt = mysqli_prepare(
                    $dbConn,
                    'INSERT INTO DRIVER (name, email, password, phone_number, approved_by, driver_status, nric_number, nric_front_image, nric_back_image, license_front_image, license_back_image, license_expiry_date, vehicle_model, plate_number, color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'ssssissssssssss', $name, $email, $passwordHash, $phone, $approvedBy, $status, $nricNumber, $placeholderImage, $placeholderImage, $placeholderImage, $placeholderImage, $licenseExpiryDate, $vehicleModel, $plateNumber, $color);
                    $userMessage = mysqli_stmt_execute($stmt) ? 'Driver created successfully.' : 'Unable to create driver. Please check for duplicate email, NRIC or plate values.';
                    mysqli_stmt_close($stmt);
                } else {
                    $userError = 'Unable to prepare driver creation.';
                }
                if ($userMessage === '' && $userError === '') {
                    $userError = 'Unable to create driver. Please check for duplicate email, NRIC or plate values.';
                }
            }
        } else {
            $userError = 'Invalid user role selected.';
        }
    }

    if (isset($_POST['update_user'])) {
        $role = strtolower(trim((string) ($_POST['role'] ?? '')));
        $targetId = (int) ($_POST['target_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordHash = $password !== '' ? md5($password) : '';
        $status = strtolower(trim((string) ($_POST['status'] ?? '')));

        if ($targetId <= 0 || $name === '' || $email === '' || $phone === '') {
            $userError = 'Edit form is incomplete.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $userError = 'Please enter a valid email address.';
        } elseif (!in_array($status, adminUserAllowedStatuses($role), true)) {
            $userError = 'Invalid status selected for this user.';
        } elseif ($role === 'rider') {
            $sql = $password !== ''
                ? 'UPDATE RIDER SET name = ?, email = ?, phone_number = ?, rider_status = ?, password = ? WHERE rider_id = ?'
                : 'UPDATE RIDER SET name = ?, email = ?, phone_number = ?, rider_status = ? WHERE rider_id = ?';
            $stmt = mysqli_prepare($dbConn, $sql);

            if ($stmt) {
                if ($password !== '') {
                    mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $phone, $status, $passwordHash, $targetId);
                } else {
                    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $phone, $status, $targetId);
                }

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    adminUserRedirect();
                }

                mysqli_stmt_close($stmt);
                $userError = 'Unable to update rider. Please check for duplicate email values.';
            } else {
                $userError = 'Unable to prepare rider update.';
            }
        } elseif ($role === 'driver') {
            $nricNumber = trim((string) ($_POST['nric_number'] ?? ''));
            $licenseExpiryDate = trim((string) ($_POST['license_expiry_date'] ?? ''));
            $vehicleModel = trim((string) ($_POST['vehicle_model'] ?? ''));
            $plateNumber = strtoupper(trim((string) ($_POST['plate_number'] ?? '')));
            $color = trim((string) ($_POST['color'] ?? ''));
            $approvedBy = adminCurrentAdminId();

            if ($nricNumber === '' || $licenseExpiryDate === '' || $plateNumber === '') {
                $userError = 'Driver NRIC, license expiry date and plate number are required.';
            } else {
                $sql = $password !== ''
                    ? 'UPDATE DRIVER SET name = ?, email = ?, phone_number = ?, driver_status = ?, password = ?, approved_by = ?, nric_number = ?, license_expiry_date = ?, vehicle_model = ?, plate_number = ?, color = ? WHERE driver_id = ?'
                    : 'UPDATE DRIVER SET name = ?, email = ?, phone_number = ?, driver_status = ?, approved_by = ?, nric_number = ?, license_expiry_date = ?, vehicle_model = ?, plate_number = ?, color = ? WHERE driver_id = ?';
                $stmt = mysqli_prepare($dbConn, $sql);

                if ($stmt) {
                    if ($password !== '') {
                        mysqli_stmt_bind_param($stmt, 'sssssisssssi', $name, $email, $phone, $status, $passwordHash, $approvedBy, $nricNumber, $licenseExpiryDate, $vehicleModel, $plateNumber, $color, $targetId);
                    } else {
                        mysqli_stmt_bind_param($stmt, 'ssssisssssi', $name, $email, $phone, $status, $approvedBy, $nricNumber, $licenseExpiryDate, $vehicleModel, $plateNumber, $color, $targetId);
                    }

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        adminUserRedirect();
                    }

                    mysqli_stmt_close($stmt);
                    $userError = 'Unable to update driver. Please check for duplicate email, NRIC or plate values.';
                } else {
                    $userError = 'Unable to prepare driver update.';
                }
            }
        } else {
            $userError = 'Invalid edit request.';
        }
    }

    if (isset($_POST['delete_user'])) {
        $role = strtolower(trim((string) ($_POST['role'] ?? '')));
        $targetId = (int) ($_POST['target_id'] ?? 0);

        if ($targetId <= 0) {
            $userError = 'Invalid delete request.';
        } elseif ($role === 'rider') {
            if (adminDeleteRider($dbConn, $targetId)) {
                adminUserRedirect();
            }
            $userError = 'Unable to delete rider.';
        } elseif ($role === 'driver') {
            if (adminDeleteDriver($dbConn, $targetId)) {
                adminUserRedirect();
            }
            $userError = 'Unable to delete driver.';
        } else {
            $userError = 'Invalid delete request.';
        }
    }

    if (isset($_POST['change_user_status'])) {
        $targetRole = strtolower(trim((string) ($_POST['target_role'] ?? '')));
        $targetId = (int) ($_POST['target_id'] ?? 0);
        $targetStatus = strtolower(trim((string) ($_POST['target_status'] ?? '')));

        if ($targetId > 0 && in_array($targetStatus, adminUserAllowedStatuses($targetRole), true)) {
            if ($targetRole === 'rider') {
                $stmt = mysqli_prepare($dbConn, 'UPDATE RIDER SET rider_status = ? WHERE rider_id = ?');
            } elseif ($targetRole === 'driver') {
                $stmt = mysqli_prepare($dbConn, 'UPDATE DRIVER SET driver_status = ?, approved_by = ? WHERE driver_id = ?');
            } else {
                $stmt = false;
            }

            if ($stmt) {
                if ($targetRole === 'driver') {
                    $approvedBy = adminCurrentAdminId();
                    mysqli_stmt_bind_param($stmt, 'sii', $targetStatus, $approvedBy, $targetId);
                } else {
                    mysqli_stmt_bind_param($stmt, 'si', $targetStatus, $targetId);
                }

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    adminUserRedirect();
                }

                mysqli_stmt_close($stmt);
                $userError = 'Unable to update user status.';
            } else {
                $userError = 'Invalid user status request.';
            }
        } else {
            $userError = 'Invalid user status request.';
        }
    }
}

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
        r.phone_number,
        r.profile_photo,
        r.rider_status,
        COALESCE(SUM(rr.amount_paid), 0) AS wallet_amount,
        COALESCE(SUM(rg.points_change), 0) AS green_points,
        COUNT(DISTINCT rr.request_id) AS trip_count
     FROM RIDER r
     LEFT JOIN RIDE_REQUEST rr ON rr.rider_id = r.rider_id AND rr.request_status = 'approved'
     LEFT JOIN RIDER_GREEN_POINT_LOG rg ON rg.rider_id = r.rider_id
     GROUP BY r.rider_id, r.name, r.email, r.phone_number, r.profile_photo, r.rider_status
     ORDER BY r.name ASC"
);

$drivers = adminFetchAll(
    $dbConn,
    "SELECT
        d.driver_id,
        d.name,
        d.email,
        d.phone_number,
        d.profile_photo,
        d.driver_status,
        d.nric_number,
        d.plate_number,
        d.vehicle_model,
        d.color,
        d.license_expiry_date
     FROM DRIVER d
     ORDER BY d.name ASC"
);

$pendingDrivers = adminFetchAll(
    $dbConn,
    "SELECT driver_id, name, email, phone_number, profile_photo, nric_number, plate_number, vehicle_model, created_at, driver_status, color, license_expiry_date
     FROM DRIVER
     WHERE driver_status = ?
     ORDER BY created_at DESC",
    's',
    ['pending']
);

$editRole = strtolower(trim((string) ($_GET['edit_role'] ?? '')));
$editId = (int) ($_GET['edit_id'] ?? 0);
$editUser = null;

if ($editId > 0 && in_array($editRole, ['rider', 'driver'], true)) {
    if ($editRole === 'rider') {
        $editUser = adminFetchOne($dbConn, 'SELECT rider_id AS id, name, email, phone_number AS phone, rider_status AS status, password FROM RIDER WHERE rider_id = ?', 'i', [$editId]);
    } else {
        $editUser = adminFetchOne($dbConn, 'SELECT driver_id AS id, name, email, phone_number AS phone, driver_status AS status, password, nric_number, license_expiry_date, vehicle_model, plate_number, color FROM DRIVER WHERE driver_id = ?', 'i', [$editId]);
    }
}
?>
  <main class="dashboard-main">
    <h1 class="page-title">User Management</h1>
    <p class="page-subtitle">Create, edit, manage and delete riders and drivers with live database data.</p>

    <?php if ($userMessage !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(200,241,53,0.2)"><?php echo adminEscape($userMessage); ?></div>
    <?php } ?>
    <?php if ($userError !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(255,71,87,0.3)"><?php echo adminEscape($userError); ?></div>
    <?php } ?>

    <div class="card" id="user-creator">
      <div class="card-header"><span class="card-title">Create User</span></div>
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="create_user" value="1">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Role</label>
              <select class="form-select" name="role" id="createUserRole" onchange="adminToggleUserFields('createUserRole','create-driver-fields')">
                <option value="rider">Rider</option>
                <option value="driver">Driver</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="banned">Banned</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Name</label><input class="form-input" name="name" required></div>
            <div class="form-group"><label class="form-label">Email</label><input class="form-input" type="email" name="email" required></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Phone</label><input class="form-input" name="phone" required></div>
            <div class="form-group"><label class="form-label">Password</label><input class="form-input" type="text" name="password" required></div>
          </div>
          <div class="create-driver-fields" style="display:none">
            <div class="form-row">
              <div class="form-group"><label class="form-label">NRIC Number</label><input class="form-input" name="nric_number"></div>
              <div class="form-group"><label class="form-label">License Expiry Date</label><input class="form-input" type="date" name="license_expiry_date"></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">Vehicle Model</label><input class="form-input" name="vehicle_model"></div>
              <div class="form-group"><label class="form-label">Plate Number</label><input class="form-input" name="plate_number"></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">Color</label><input class="form-input" name="color"></div>
              <div class="form-group"><label class="form-label">Document Note</label><div class="form-hint">New drivers use the default profile image as a placeholder for required document blobs.</div></div>
            </div>
          </div>
          <button class="btn btn-lime" type="submit">Create User</button>
        </form>
      </div>
    </div>

    <?php if ($editUser !== null) { ?>
      <div class="card" id="user-editor">
        <div class="card-header">
          <span class="card-title">Edit <?php echo adminEscape(ucfirst($editRole)); ?></span>
          <a class="btn btn-xs" href="users.php">Cancel</a>
        </div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="update_user" value="1">
            <input type="hidden" name="role" value="<?php echo adminEscape($editRole); ?>">
            <input type="hidden" name="target_id" value="<?php echo adminEscape($editUser['id']); ?>">
            <div class="form-row">
              <div class="form-group"><label class="form-label">Name</label><input class="form-input" name="name" value="<?php echo adminEscape($editUser['name']); ?>" required></div>
              <div class="form-group"><label class="form-label">Email</label><input class="form-input" type="email" name="email" value="<?php echo adminEscape($editUser['email']); ?>" required></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">Phone</label><input class="form-input" name="phone" value="<?php echo adminEscape($editUser['phone']); ?>" required></div>
              <div class="form-group">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                  <?php foreach (adminUserAllowedStatuses($editRole) as $statusOption) { ?>
                    <option value="<?php echo adminEscape($statusOption); ?>" <?php echo $editUser['status'] === $statusOption ? 'selected' : ''; ?>><?php echo adminEscape(ucfirst($statusOption)); ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">New Password</label><input class="form-input" type="text" name="password" placeholder="Leave blank to keep current password"></div>
              <div class="form-group"><label class="form-label">Role</label><input class="form-input" value="<?php echo adminEscape(ucfirst($editRole)); ?>" disabled></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">Current Password (Hashed)</label><input class="form-input" value="<?php echo adminEscape($editUser['password'] ?? ''); ?>" readonly></div>
            </div>

            <?php if ($editRole === 'driver') { ?>
              <div class="form-row">
                <div class="form-group"><label class="form-label">NRIC Number</label><input class="form-input" name="nric_number" value="<?php echo adminEscape($editUser['nric_number']); ?>" required></div>
                <div class="form-group"><label class="form-label">License Expiry Date</label><input class="form-input" type="date" name="license_expiry_date" value="<?php echo adminEscape($editUser['license_expiry_date']); ?>" required></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Vehicle Model</label><input class="form-input" name="vehicle_model" value="<?php echo adminEscape($editUser['vehicle_model']); ?>"></div>
                <div class="form-group"><label class="form-label">Plate Number</label><input class="form-input" name="plate_number" value="<?php echo adminEscape($editUser['plate_number']); ?>" required></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Color</label><input class="form-input" name="color" value="<?php echo adminEscape($editUser['color']); ?>"></div>
              </div>
            <?php } ?>

            <button class="btn btn-lime" type="submit">Save Changes</button>
          </form>
        </div>
      </div>
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
            <thead><tr><th>User</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($allUsers as $user) { ?>
                <?php $roleKey = strtolower($user['role']); ?>
                <tr>
                  <td><div class="user-cell"><img class="user-avatar" src="<?php echo adminEscape(adminAvatarSrc($user['profile_photo'])); ?>" alt="User avatar" onerror="this.src='../../public-assets/images/profile-icon.png'"><div><div class="user-name"><?php echo adminEscape($user['name']); ?></div><div class="user-sub">#<?php echo adminEscape(strtoupper(substr($user['role'], 0, 1)) . '-' . str_pad((string) $user['id'], 4, '0', STR_PAD_LEFT)); ?></div></div></div></td>
                  <td><?php echo adminEscape($user['email']); ?></td>
                  <td><?php echo adminEscape($user['phone']); ?></td>
                  <td><span class="badge <?php echo adminEscape(adminRoleBadgeClass($user['role'])); ?>"><?php echo adminEscape($user['role']); ?></span></td>
                  <td><span class="badge <?php echo adminEscape(adminStatusBadgeClass($user['status'])); ?>"><span class="bdot"></span> <?php echo adminEscape(ucfirst($user['status'])); ?></span></td>
                  <td>
                    <div class="action-btns">
                      <a class="btn btn-xs" href="users.php?edit_role=<?php echo adminEscape($roleKey); ?>&edit_id=<?php echo adminEscape($user['id']); ?>#user-editor">Edit</a>
                      <form method="post">
                        <input type="hidden" name="delete_user" value="1">
                        <input type="hidden" name="role" value="<?php echo adminEscape($roleKey); ?>">
                        <input type="hidden" name="target_id" value="<?php echo adminEscape($user['id']); ?>">
                        <button class="btn btn-xs btn-danger" type="submit">Delete</button>
                      </form>
                      <?php if ($user['status'] !== 'active') { ?>
                        <form method="post">
                          <input type="hidden" name="change_user_status" value="1">
                          <input type="hidden" name="target_role" value="<?php echo adminEscape($roleKey); ?>">
                          <input type="hidden" name="target_id" value="<?php echo adminEscape($user['id']); ?>">
                          <input type="hidden" name="target_status" value="active">
                          <button class="btn btn-xs btn-lime" type="submit">Activate</button>
                        </form>
                      <?php } ?>
                      <?php if ($user['status'] !== 'banned') { ?>
                        <form method="post">
                          <input type="hidden" name="change_user_status" value="1">
                          <input type="hidden" name="target_role" value="<?php echo adminEscape($roleKey); ?>">
                          <input type="hidden" name="target_id" value="<?php echo adminEscape($user['id']); ?>">
                          <input type="hidden" name="target_status" value="banned">
                          <button class="btn btn-xs btn-danger" type="submit">Ban</button>
                        </form>
                      <?php } ?>
                    </div>
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
            <thead><tr><th>Rider</th><th>Email</th><th>Phone</th><th>Wallet</th><th>Green Points</th><th>Trips</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($riders as $rider) { ?>
                <tr>
                  <td><div class="user-cell"><img class="user-avatar" src="<?php echo adminEscape(adminAvatarSrc($rider['profile_photo'])); ?>" alt="Rider avatar" onerror="this.src='../../public-assets/images/profile-icon.png'"><div><div class="user-name"><?php echo adminEscape($rider['name']); ?></div><div class="user-sub">#R-<?php echo adminEscape(str_pad((string) $rider['rider_id'], 4, '0', STR_PAD_LEFT)); ?></div></div></div></td>
                  <td><?php echo adminEscape($rider['email']); ?></td>
                  <td><?php echo adminEscape($rider['phone_number']); ?></td>
                  <td style="font-family:'DM Mono',monospace">RM <?php echo adminEscape(number_format((float) $rider['wallet_amount'], 2)); ?></td>
                  <td><span class="badge b-lime"><?php echo adminEscape(number_format((int) $rider['green_points'])); ?> pts</span></td>
                  <td><?php echo adminEscape((int) $rider['trip_count']); ?></td>
                  <td><span class="badge <?php echo adminEscape(adminStatusBadgeClass($rider['rider_status'])); ?>"><span class="bdot"></span> <?php echo adminEscape(ucfirst($rider['rider_status'])); ?></span></td>
                  <td>
                    <div class="action-btns">
                      <a class="btn btn-xs" href="users.php?edit_role=rider&edit_id=<?php echo adminEscape($rider['rider_id']); ?>#user-editor">Edit</a>
                      <form method="post"><input type="hidden" name="delete_user" value="1"><input type="hidden" name="role" value="rider"><input type="hidden" name="target_id" value="<?php echo adminEscape($rider['rider_id']); ?>"><button class="btn btn-xs btn-danger" type="submit">Delete</button></form>
                      <?php if ($rider['rider_status'] !== 'active') { ?>
                        <form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="rider"><input type="hidden" name="target_id" value="<?php echo adminEscape($rider['rider_id']); ?>"><input type="hidden" name="target_status" value="active"><button class="btn btn-xs btn-lime" type="submit">Activate</button></form>
                      <?php } ?>
                      <?php if ($rider['rider_status'] !== 'banned') { ?>
                        <form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="rider"><input type="hidden" name="target_id" value="<?php echo adminEscape($rider['rider_id']); ?>"><input type="hidden" name="target_status" value="banned"><button class="btn btn-xs btn-danger" type="submit">Ban</button></form>
                      <?php } ?>
                    </div>
                  </td>
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
            <thead><tr><th>Driver</th><th>Email</th><th>Phone</th><th>Plate</th><th>Vehicle</th><th>License Expiry</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($drivers as $driver) { ?>
                <tr>
                  <td><div class="user-cell"><img class="user-avatar" src="<?php echo adminEscape(adminAvatarSrc($driver['profile_photo'])); ?>" alt="Driver avatar" onerror="this.src='../../public-assets/images/profile-icon.png'"><div><div class="user-name"><?php echo adminEscape($driver['name']); ?></div><div class="user-sub">#D-<?php echo adminEscape(str_pad((string) $driver['driver_id'], 4, '0', STR_PAD_LEFT)); ?></div></div></div></td>
                  <td><?php echo adminEscape($driver['email']); ?></td>
                  <td><?php echo adminEscape($driver['phone_number']); ?></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($driver['plate_number']); ?></td>
                  <td style="font-size:12px"><?php echo adminEscape(trim($driver['vehicle_model'] . ($driver['color'] !== '' ? ' · ' . $driver['color'] : ''))); ?></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($driver['license_expiry_date']); ?></td>
                  <td><span class="badge <?php echo adminEscape(adminStatusBadgeClass($driver['driver_status'])); ?>"><span class="bdot"></span> <?php echo adminEscape(ucfirst($driver['driver_status'])); ?></span></td>
                  <td>
                    <div class="action-btns">
                      <a class="btn btn-xs" href="users.php?edit_role=driver&edit_id=<?php echo adminEscape($driver['driver_id']); ?>#user-editor">Edit</a>
                      <form method="post"><input type="hidden" name="delete_user" value="1"><input type="hidden" name="role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($driver['driver_id']); ?>"><button class="btn btn-xs btn-danger" type="submit">Delete</button></form>
                      <?php if ($driver['driver_status'] !== 'active') { ?>
                        <form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($driver['driver_id']); ?>"><input type="hidden" name="target_status" value="active"><button class="btn btn-xs btn-lime" type="submit">Activate</button></form>
                      <?php } ?>
                      <?php if ($driver['driver_status'] !== 'banned') { ?>
                        <form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($driver['driver_id']); ?>"><input type="hidden" name="target_status" value="banned"><button class="btn btn-xs btn-danger" type="submit">Ban</button></form>
                      <?php } ?>
                    </div>
                  </td>
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
            <thead><tr><th>Applicant</th><th>Email</th><th>Phone</th><th>Plate</th><th>Vehicle</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($pendingDrivers as $pendingDriver) { ?>
                <tr>
                  <td><div class="user-cell"><img class="user-avatar" src="<?php echo adminEscape(adminAvatarSrc($pendingDriver['profile_photo'])); ?>" alt="Applicant avatar" onerror="this.src='../../public-assets/images/profile-icon.png'"><div><div class="user-name"><?php echo adminEscape($pendingDriver['name']); ?></div><div class="user-sub">#D-<?php echo adminEscape(str_pad((string) $pendingDriver['driver_id'], 4, '0', STR_PAD_LEFT)); ?></div></div></div></td>
                  <td><?php echo adminEscape($pendingDriver['email']); ?></td>
                  <td><?php echo adminEscape($pendingDriver['phone_number']); ?></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($pendingDriver['plate_number']); ?></td>
                  <td style="font-size:12px"><?php echo adminEscape(trim($pendingDriver['vehicle_model'] . ($pendingDriver['color'] !== '' ? ' · ' . $pendingDriver['color'] : ''))); ?></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($pendingDriver['created_at']); ?></td>
                  <td><span class="badge <?php echo adminEscape(adminStatusBadgeClass($pendingDriver['driver_status'])); ?>"><span class="bdot"></span> <?php echo adminEscape(ucfirst($pendingDriver['driver_status'])); ?></span></td>
                  <td><div class="action-btns"><a class="btn btn-xs" href="users.php?edit_role=driver&edit_id=<?php echo adminEscape($pendingDriver['driver_id']); ?>#user-editor">Edit</a><form method="post"><input type="hidden" name="delete_user" value="1"><input type="hidden" name="role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($pendingDriver['driver_id']); ?>"><button class="btn btn-xs btn-danger" type="submit">Delete</button></form><form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($pendingDriver['driver_id']); ?>"><input type="hidden" name="target_status" value="active"><button class="btn btn-xs btn-lime" type="submit">Approve</button></form><form method="post"><input type="hidden" name="change_user_status" value="1"><input type="hidden" name="target_role" value="driver"><input type="hidden" name="target_id" value="<?php echo adminEscape($pendingDriver['driver_id']); ?>"><input type="hidden" name="target_status" value="rejected"><button class="btn btn-xs btn-warn" type="submit">Reject</button></form></div></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script>
    function adminToggleUserFields(selectId, className) {
      var select = document.getElementById(selectId);
      var show = select && select.value === 'driver';
      document.querySelectorAll('.' + className).forEach(function (section) {
        section.style.display = show ? '' : 'none';
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      adminToggleUserFields('createUserRole', 'create-driver-fields');
    });
  </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
