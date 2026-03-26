<?php
$pageTitle = 'Settings';
$activePage = 'settings';
require_once __DIR__ . '/includes/header.php';

$settingsMessage = '';
$settingsError = '';

$currentMultiplier = (float) (adminFetchOne($dbConn, 'SELECT multiplier_value FROM GREEN_POINT_CONFIG LIMIT 1')['multiplier_value'] ?? 1.0);
$systemConfig = adminFetchOne($dbConn, 'SELECT driver_registration FROM SYSTEM_CONFIG LIMIT 1');
$driverRegistration = isset($systemConfig['driver_registration']) ? (int) $systemConfig['driver_registration'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $currentMultiplier = max(0.5, min(5.0, (float) ($_POST['multiplier_value'] ?? 1.0)));
    $driverRegistration = isset($_POST['driver_registration']) ? 1 : 0;

    $greenCount = (int) (adminFetchOne($dbConn, 'SELECT COUNT(*) AS total FROM GREEN_POINT_CONFIG')['total'] ?? 0);
    if ($greenCount > 0) {
        $stmt = mysqli_prepare($dbConn, 'UPDATE GREEN_POINT_CONFIG SET multiplier_value = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'd', $currentMultiplier);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } else {
        $stmt = mysqli_prepare($dbConn, 'INSERT INTO GREEN_POINT_CONFIG (multiplier_value) VALUES (?)');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'd', $currentMultiplier);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $systemCount = (int) (adminFetchOne($dbConn, 'SELECT COUNT(*) AS total FROM SYSTEM_CONFIG')['total'] ?? 0);
    if ($systemCount > 0) {
        $stmt = mysqli_prepare($dbConn, 'UPDATE SYSTEM_CONFIG SET driver_registration = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $driverRegistration);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $settingsMessage = 'Settings saved successfully.';
        } else {
            $settingsError = 'Unable to update system settings.';
        }
    } else {
        $stmt = mysqli_prepare($dbConn, 'INSERT INTO SYSTEM_CONFIG (driver_registration) VALUES (?)');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $driverRegistration);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $settingsMessage = 'Settings saved successfully.';
        } else {
            $settingsError = 'Unable to insert system settings.';
        }
    }
}
?>
  <main class="dashboard-main">
    <h1 class="page-title">Settings</h1>
    <p class="page-subtitle">Platform configuration backed by the live settings tables.</p>

    <?php if ($settingsMessage !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(200,241,53,0.2)"><?php echo adminEscape($settingsMessage); ?></div>
    <?php } ?>
    <?php if ($settingsError !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(255,71,87,0.3)"><?php echo adminEscape($settingsError); ?></div>
    <?php } ?>

    <form method="post">
      <input type="hidden" name="save_settings" value="1">

      <div class="section-block">
        <div class="section-title">Green Point Multiplier</div>
        <div class="multiplier-box">
          <p style="font-size:13px;color:var(--gray-400);margin-bottom:4px">Adjust the reward multiplier applied to rides.</p>
          <div class="multiplier-display" id="multiplierDisplay"><?php echo adminEscape(number_format($currentMultiplier, 1)); ?>x</div>
          <input type="range" class="multiplier-range" id="multiplierRange" name="multiplier_value" min="0.5" max="5" step="0.1" value="<?php echo adminEscape(number_format($currentMultiplier, 1, '.', '')); ?>" oninput="updateMultiplier(this.value)">
          <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--gray-500);font-family:'DM Mono',monospace;margin-bottom:6px">
            <span>0.5x</span><span>1.0x</span><span>2.0x</span><span>3.0x</span><span>4.0x</span><span>5.0x</span>
          </div>
          <div class="calc-row">
            <span class="calc-num">1 ride</span>
            <span class="calc-op">=</span>
            <span class="calc-num">100 pts</span>
            <span class="calc-op">x</span>
            <span class="calc-num" id="calcMultiplier" style="color:var(--lime)"><?php echo adminEscape(number_format($currentMultiplier, 1)); ?></span>
            <span class="calc-op">=</span>
            <span class="calc-result" id="calcResult"><?php echo adminEscape((string) round(100 * $currentMultiplier)); ?> pts / ride</span>
          </div>
        </div>
      </div>

      <div class="section-block">
        <div class="section-title">General</div>
        <div class="card">
          <div class="card-body">
            <div class="setting-row">
              <div class="setting-info"><div class="setting-title">New Driver Registrations</div><div class="setting-desc">Allow new drivers to submit registration applications.</div></div>
              <label class="toggle"><input type="checkbox" name="driver_registration" <?php echo $driverRegistration ? 'checked' : ''; ?>><span class="toggle-slider"></span></label>
            </div>
            <button class="btn btn-lime" type="submit">Save Settings</button>
          </div>
        </div>
      </div>
    </form>
  </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
