<?php
$pageTitle = 'Profile';
$activePage = 'profile';
require_once __DIR__ . '/includes/header.php';

$profileMessage = '';
$profileError = '';
$adminId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile']) && $adminId > 0) {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($name === '' || $email === '') {
        $profileError = 'Name and email are required.';
    } else {
        $stmt = mysqli_prepare($dbConn, 'UPDATE ADMIN SET name = ?, email = ? WHERE admin_id = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $email, $adminId);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['user'] = $name;
                $profileMessage = 'Profile updated successfully.';
            } else {
                $profileError = 'Unable to update profile.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $profileError = 'Unable to prepare profile update.';
        }
    }
}

$adminProfile = null;
if ($adminId > 0) {
    $adminProfile = adminFetchOne($dbConn, 'SELECT admin_id, name, email, created_at FROM ADMIN WHERE admin_id = ?', 'i', [$adminId]);
}
if ($adminProfile === null) {
    $adminProfile = adminFetchOne($dbConn, 'SELECT admin_id, name, email, created_at FROM ADMIN ORDER BY admin_id ASC LIMIT 1');
}
$adminName = $adminProfile['name'] ?? 'Admin User';
$adminEmail = $adminProfile['email'] ?? 'admin@comove.local';
$adminCreatedAt = $adminProfile['created_at'] ?? '';
$adminCode = isset($adminProfile['admin_id']) ? 'ADMIN-' . str_pad((string) $adminProfile['admin_id'], 3, '0', STR_PAD_LEFT) : 'ADMIN-000';
$adminInitials = strtoupper(substr(preg_replace('/\s+/', '', $adminName), 0, 2));
?>
  <main class="dashboard-main">
    <h1 class="page-title">Admin Profile</h1>
    <p class="page-subtitle">Your account information from the live admin table.</p>

    <?php if ($profileMessage !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(200,241,53,0.2)"><?php echo adminEscape($profileMessage); ?></div>
    <?php } ?>
    <?php if ($profileError !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(255,71,87,0.3)"><?php echo adminEscape($profileError); ?></div>
    <?php } ?>

    <div class="card" style="max-width:640px">
      <div class="card-body">
        <div style="display:flex;align-items:center;gap:18px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border)">
          <div class="profile-avatar-lg"><?php echo adminEscape($adminInitials !== '' ? $adminInitials : 'AD'); ?></div>
          <div>
            <div style="font-family:'Barlow Condensed',sans-serif;font-size:24px;font-weight:900;text-transform:uppercase;letter-spacing:-0.02em"><?php echo adminEscape($adminName); ?></div>
            <div style="font-family:'DM Mono',monospace;font-size:11px;color:var(--gray-500);margin-top:2px">#<?php echo adminEscape($adminCode); ?> · System Administrator</div>
            <div style="margin-top:8px"><span class="badge b-lime">Admin</span></div>
          </div>
        </div>

        <div class="info-grid" style="margin-bottom:20px">
          <div class="info-block"><div class="info-block-label">Email</div><div class="info-block-val"><?php echo adminEscape($adminEmail); ?></div></div>
          <div class="info-block"><div class="info-block-label">Role</div><div class="info-block-val">Administrator</div></div>
          <div class="info-block"><div class="info-block-label">Status</div><div class="info-block-val"><span class="badge b-lime"><span class="bdot"></span> Active</span></div></div>
          <div class="info-block"><div class="info-block-label">Created</div><div class="info-block-val" style="font-family:'DM Mono',monospace;font-size:12px"><?php echo adminEscape($adminCreatedAt); ?></div></div>
          <div class="info-block"><div class="info-block-label">Session User</div><div class="info-block-val"><?php echo adminEscape((string) ($_SESSION['user'] ?? $adminName)); ?></div></div>
          <div class="info-block"><div class="info-block-label">Admin ID</div><div class="info-block-val"><?php echo adminEscape($adminCode); ?></div></div>
        </div>

        <form method="post">
          <input type="hidden" name="save_profile" value="1">
          <div class="form-row">
            <div class="form-group"><label class="form-label">Name</label><input class="form-input" name="name" value="<?php echo adminEscape($adminName); ?>"></div>
            <div class="form-group"><label class="form-label">Email</label><input class="form-input" type="email" name="email" value="<?php echo adminEscape($adminEmail); ?>"></div>
          </div>
          <button class="btn btn-lime" type="submit">Save Changes</button>
        </form>
      </div>
    </div>
  </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
