<?php
require_once __DIR__ . '/_bootstrap.php';

$riderId = riderCurrentId();

$pointsRow = riderFetchOne("SELECT COALESCE(SUM(points_change), 0) AS total_points FROM RIDER_GREEN_POINT_LOG WHERE rider_id = {$riderId}");
$points = isset($pointsRow['total_points']) ? (int) $pointsRow['total_points'] : 0;
$level = riderLevelInfo($points);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rewardId = isset($_POST['reward_id']) ? (int) $_POST['reward_id'] : 0;
    if ($rewardId <= 0) {
        riderError('Invalid reward.');
    }

    $reward = riderFetchOne("SELECT reward_id, reward_name, points_required, stock FROM REWARD WHERE reward_id = {$rewardId} LIMIT 1");
    if (!$reward) {
        riderError('Reward not found.', 404);
    }
    if ((int) $reward['stock'] <= 0) {
        riderError('Reward is out of stock.');
    }
    if ($points < (int) $reward['points_required']) {
        riderError('Not enough green points.');
    }

    mysqli_query($dbConn, "INSERT INTO RIDER_REDEMPTION (rider_id, reward_id) VALUES ({$riderId}, {$rewardId})");
    mysqli_query($dbConn, "UPDATE REWARD SET stock = stock - 1 WHERE reward_id = {$rewardId} LIMIT 1");
    mysqli_query($dbConn, "INSERT INTO RIDER_GREEN_POINT_LOG (rider_id, points_change, source) VALUES ({$riderId}, -" . (int) $reward['points_required'] . ", 'Redeemed " . riderEsc($reward['reward_name']) . "')");

    riderSuccess([
        'message' => $reward['reward_name'] . ' redeemed successfully.',
    ]);
}

$rewardsRaw = riderFetchAll("
    SELECT reward_id, reward_name, points_required, category, stock
    FROM REWARD
    ORDER BY points_required ASC, reward_name ASC
");

$historyRaw = riderFetchAll("
    SELECT source, points_change, created_at
    FROM RIDER_GREEN_POINT_LOG
    WHERE rider_id = {$riderId}
    ORDER BY created_at DESC
    LIMIT 10
");

$rewards = [];
foreach ($rewardsRaw as $reward) {
    $rewards[] = [
        'reward_id' => (int) $reward['reward_id'],
        'name' => $reward['reward_name'],
        'cost' => (int) $reward['points_required'],
        'category' => $reward['category'],
        'stock' => (int) $reward['stock'],
        'can_redeem' => $points >= (int) $reward['points_required'] && (int) $reward['stock'] > 0,
    ];
}

$history = [];
foreach ($historyRaw as $entry) {
    $history[] = [
        'label' => $entry['source'],
        'date' => $entry['created_at'],
        'points_change' => (int) $entry['points_change'],
    ];
}

riderSuccess([
    'green_points' => $points,
    'level' => $level,
    'rewards' => $rewards,
    'history' => $history,
]);
