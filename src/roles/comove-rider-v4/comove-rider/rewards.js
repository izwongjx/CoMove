/* Comove – Rewards JS */
function initRiderRewards() {
  loadRewards();
}

async function loadRewards() {
  try {
    var data = await apiGet('api/rewards.php');
    renderRewards(data);
  } catch (err) {
    showToast('⚠️ Unable to load rewards');
  }
}

function renderRewards(data) {
  document.getElementById('totalPoints').textContent = data.green_points;
  document.getElementById('rewardsLevelBadge').textContent = '🌿 ' + data.level.title + ' · Level ' + data.level.level;
  document.getElementById('rewardsProgressBar').style.width = Math.max(0, Math.min(100, data.level.progress_percent)) + '%';
  document.getElementById('rewardsProgressLabel').textContent =
    data.green_points + ' / ' + (data.level.current_max === null ? data.green_points : data.level.current_max + 1) +
    ' pts · ' + data.level.points_to_next + ' pts to ' + data.level.next_title;

  document.getElementById('rewardsList').innerHTML = (data.rewards || []).map(function(reward) {
    return '<div class="reward-card">'
      + '<div class="reward-icon">🎁</div>'
      + '<div class="reward-info"><div class="reward-name">' + escapeHtml(reward.name) + '</div><div class="reward-desc">' + escapeHtml(reward.category) + ' · Stock ' + reward.stock + '</div></div>'
      + '<div class="reward-actions"><div class="reward-cost">' + reward.cost + ' pts</div>'
      + (reward.can_redeem
        ? '<button class="btn-sm primary" onclick="redeemReward(' + reward.reward_id + ', \'' + escapeHtml(reward.name) + '\')">Redeem</button>'
        : '<button class="btn-sm ghost">Need More</button>')
      + '</div></div>';
  }).join('');

  document.getElementById('pointsHistory').innerHTML = (data.history || []).map(function(entry) {
    var cls = entry.points_change >= 0 ? 'history-pts-pos' : 'history-pts-neg';
    var value = entry.points_change >= 0 ? '+' + entry.points_change : String(entry.points_change);
    return '<div class="history-row">'
      + '<div><div style="font-size:14px;font-weight:600;">' + escapeHtml(entry.label) + '</div><div style="font-size:12px;color:var(--gray-400);">' + escapeHtml(entry.date) + '</div></div>'
      + '<div class="' + cls + '">' + value + '</div>'
      + '</div>';
  }).join('') || '<div class="history-row"><div style="font-size:14px;">No points history yet.</div></div>';
}

async function redeemReward(rewardId, rewardName) {
  var formData = new FormData();
  formData.append('reward_id', rewardId);

  try {
    await apiPost('api/rewards.php', formData);
    showToast('🎉 ' + rewardName + ' redeemed!');
    loadRewards();
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

document.addEventListener('DOMContentLoaded', initRiderRewards);
