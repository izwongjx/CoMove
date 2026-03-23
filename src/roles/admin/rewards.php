<?php
$pageTitle = 'Rewards';
$activePage = 'rewards';
require_once __DIR__ . '/includes/header.php';

$rewardMessage = '';
$rewardError = '';

function adminRewardRedirect(): void
{
    header('Location: rewards.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_reward'])) {
        $rewardName = trim((string) ($_POST['reward_name'] ?? ''));
        $pointsRequired = (int) ($_POST['points_required'] ?? 0);
        $category = trim((string) ($_POST['category'] ?? 'General'));
        $stock = max(0, (int) ($_POST['stock'] ?? 0));

        if ($rewardName === '' || $pointsRequired <= 0) {
            $rewardError = 'Reward name and points are required.';
        } else {
            $stmt = mysqli_prepare($dbConn, 'INSERT INTO REWARD (reward_name, points_required, category, stock) VALUES (?, ?, ?, ?)');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sisi', $rewardName, $pointsRequired, $category, $stock);
                $rewardMessage = mysqli_stmt_execute($stmt) ? 'Reward created successfully.' : '';
                $rewardError = $rewardMessage === '' ? 'Unable to create reward.' : '';
                mysqli_stmt_close($stmt);
            } else {
                $rewardError = 'Unable to prepare reward insert.';
            }
        }
    }

    if (isset($_POST['update_reward'])) {
        $rewardId = (int) ($_POST['reward_id'] ?? 0);
        $rewardName = trim((string) ($_POST['reward_name'] ?? ''));
        $pointsRequired = (int) ($_POST['points_required'] ?? 0);
        $category = trim((string) ($_POST['category'] ?? 'General'));
        $stock = max(0, (int) ($_POST['stock'] ?? 0));

        if ($rewardId <= 0 || $rewardName === '' || $pointsRequired <= 0) {
            $rewardError = 'Reward edit form is incomplete.';
        } else {
            $stmt = mysqli_prepare($dbConn, 'UPDATE REWARD SET reward_name = ?, points_required = ?, category = ?, stock = ? WHERE reward_id = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'sisii', $rewardName, $pointsRequired, $category, $stock, $rewardId);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    adminRewardRedirect();
                }
                mysqli_stmt_close($stmt);
                $rewardError = 'Unable to update reward.';
            } else {
                $rewardError = 'Unable to prepare reward update.';
            }
        }
    }

    if (isset($_POST['delete_reward'])) {
        $rewardId = (int) ($_POST['reward_id'] ?? 0);

        if ($rewardId > 0) {
            mysqli_begin_transaction($dbConn);

            try {
                if (!adminExecuteStatement($dbConn, 'DELETE FROM RIDER_REDEMPTION WHERE reward_id = ?', 'i', [$rewardId])) {
                    throw new RuntimeException('Unable to delete rider redemptions.');
                }
                if (!adminExecuteStatement($dbConn, 'DELETE FROM DRIVER_REDEMPTION WHERE reward_id = ?', 'i', [$rewardId])) {
                    throw new RuntimeException('Unable to delete driver redemptions.');
                }
                if (!adminExecuteStatement($dbConn, 'DELETE FROM REWARD WHERE reward_id = ?', 'i', [$rewardId])) {
                    throw new RuntimeException('Unable to delete reward.');
                }

                mysqli_commit($dbConn);
                adminRewardRedirect();
            } catch (Throwable $exception) {
                mysqli_rollback($dbConn);
                $rewardError = 'Unable to delete reward.';
            }
        } else {
            $rewardError = 'Invalid delete request.';
        }
    }
}

$rewards = adminFetchAll($dbConn, 'SELECT reward_id, reward_name, points_required, category, stock FROM REWARD ORDER BY reward_id DESC');
$redemptions = adminFetchAll(
    $dbConn,
    "SELECT rr.redemption_id, r.reward_name, r.points_required, rr.redeemed_at, rd.name AS user_name, 'Rider' AS role
     FROM RIDER_REDEMPTION rr
     INNER JOIN REWARD r ON r.reward_id = rr.reward_id
     INNER JOIN RIDER rd ON rd.rider_id = rr.rider_id
     UNION ALL
     SELECT dr.redemption_id, r.reward_name, r.points_required, dr.redeemed_at, dd.name AS user_name, 'Driver' AS role
     FROM DRIVER_REDEMPTION dr
     INNER JOIN REWARD r ON r.reward_id = dr.reward_id
     INNER JOIN DRIVER dd ON dd.driver_id = dr.driver_id
     ORDER BY redeemed_at DESC"
);

$editRewardId = (int) ($_GET['edit_id'] ?? 0);
$editReward = $editRewardId > 0
    ? adminFetchOne($dbConn, 'SELECT reward_id, reward_name, points_required, category, stock FROM REWARD WHERE reward_id = ?', 'i', [$editRewardId])
    : null;
?>
  <main class="dashboard-main">
    <h1 class="page-title">Reward Management</h1>
    <p class="page-subtitle">Create, edit and delete reward catalogue items from the live database.</p>

    <?php if ($rewardMessage !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(200,241,53,0.2)"><?php echo adminEscape($rewardMessage); ?></div>
    <?php } ?>
    <?php if ($rewardError !== '') { ?>
      <div class="card" style="padding:12px 18px;margin-bottom:12px;border-color:rgba(255,71,87,0.3)"><?php echo adminEscape($rewardError); ?></div>
    <?php } ?>

    <div class="sub-tabs">
      <button class="sub-tab active" id="rtab-catalogue" onclick="switchTab('rtab-','rewardsub-',['catalogue','redemptions'],'catalogue')">Catalogue</button>
      <button class="sub-tab" id="rtab-redemptions" onclick="switchTab('rtab-','rewardsub-',['catalogue','redemptions'],'redemptions')">Redemptions</button>
    </div>

    <div id="rewardsub-catalogue">
      <div class="card">
        <div class="card-header"><span class="card-title">Create Reward</span></div>
        <div class="card-body">
          <form method="post">
            <input type="hidden" name="create_reward" value="1">
            <div class="form-row">
              <div class="form-group"><label class="form-label">Reward Name</label><input class="form-input" name="reward_name" placeholder="Reward name"></div>
              <div class="form-group"><label class="form-label">Category</label><input class="form-input" name="category" placeholder="Food, Transport, etc."></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">Points Required</label><input class="form-input" type="number" name="points_required" min="1" placeholder="500"></div>
              <div class="form-group"><label class="form-label">Stock</label><input class="form-input" type="number" name="stock" min="0" placeholder="100"></div>
            </div>
            <button class="btn btn-lime" type="submit">Create Reward</button>
          </form>
        </div>
      </div>

      <?php if ($editReward !== null) { ?>
        <div class="card" id="reward-editor">
          <div class="card-header">
            <span class="card-title">Edit Reward</span>
            <a class="btn btn-xs" href="rewards.php">Cancel</a>
          </div>
          <div class="card-body">
            <form method="post">
              <input type="hidden" name="update_reward" value="1">
              <input type="hidden" name="reward_id" value="<?php echo adminEscape($editReward['reward_id']); ?>">
              <div class="form-row">
                <div class="form-group"><label class="form-label">Reward Name</label><input class="form-input" name="reward_name" value="<?php echo adminEscape($editReward['reward_name']); ?>" required></div>
                <div class="form-group"><label class="form-label">Category</label><input class="form-input" name="category" value="<?php echo adminEscape($editReward['category']); ?>" required></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label class="form-label">Points Required</label><input class="form-input" type="number" name="points_required" min="1" value="<?php echo adminEscape($editReward['points_required']); ?>" required></div>
                <div class="form-group"><label class="form-label">Stock</label><input class="form-input" type="number" name="stock" min="0" value="<?php echo adminEscape($editReward['stock']); ?>" required></div>
              </div>
              <button class="btn btn-lime" type="submit">Save Changes</button>
            </form>
          </div>
        </div>
      <?php } ?>

      <div class="card">
        <div class="card-header">
          <span class="card-title">Reward Catalogue</span>
          <div class="card-actions"><input class="search-input" placeholder="Search rewards..." oninput="filterTable('rewardsTable',this.value)"></div>
        </div>
        <div class="table-wrap">
          <table id="rewardsTable">
            <thead><tr><th>Item</th><th>Category</th><th>Points</th><th>Stock</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($rewards as $reward) { ?>
                <tr>
                  <td><strong><?php echo adminEscape($reward['reward_name']); ?></strong></td>
                  <td><?php echo adminEscape($reward['category']); ?></td>
                  <td style="font-family:'DM Mono',monospace"><?php echo adminEscape(number_format((int) $reward['points_required'])); ?></td>
                  <td style="font-family:'DM Mono',monospace"><?php echo adminEscape((int) $reward['stock']); ?></td>
                  <td>
                    <div class="action-btns">
                      <a class="btn btn-xs" href="rewards.php?edit_id=<?php echo adminEscape($reward['reward_id']); ?>#reward-editor">Edit</a>
                      <form method="post">
                        <input type="hidden" name="delete_reward" value="1">
                        <input type="hidden" name="reward_id" value="<?php echo adminEscape($reward['reward_id']); ?>">
                        <button class="btn btn-xs btn-danger" type="submit">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="rewardsub-redemptions" style="display:none">
      <div class="card">
        <div class="card-header"><span class="card-title">Redemption History</span><div class="card-actions"><input class="search-input" placeholder="Search..." oninput="filterTable('redemptionsTable',this.value)"></div></div>
        <div class="table-wrap">
          <table id="redemptionsTable">
            <thead><tr><th>ID</th><th>User</th><th>Role</th><th>Reward</th><th>Points Used</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($redemptions as $redemption) { ?>
                <tr>
                  <td style="font-family:'DM Mono',monospace">#RDM-<?php echo adminEscape(str_pad((string) $redemption['redemption_id'], 4, '0', STR_PAD_LEFT)); ?></td>
                  <td><?php echo adminEscape($redemption['user_name']); ?></td>
                  <td><span class="badge <?php echo adminEscape(adminRoleBadgeClass($redemption['role'])); ?>"><?php echo adminEscape($redemption['role']); ?></span></td>
                  <td><?php echo adminEscape($redemption['reward_name']); ?></td>
                  <td style="font-family:'DM Mono',monospace"><?php echo adminEscape(number_format((int) $redemption['points_required'])); ?></td>
                  <td style="font-family:'DM Mono',monospace;font-size:11px"><?php echo adminEscape($redemption['redeemed_at']); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
