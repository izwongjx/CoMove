/* Comove – Rewards JS */
var rewardsCache = [];

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
  rewardsCache = data.rewards || [];
  document.getElementById('totalPoints').textContent = data.green_points;
  document.getElementById('rewardsProgressLabel').textContent =
    data.green_points + ' total points';

  document.getElementById('rewardsList').innerHTML = rewardsCache.map(function(reward) {
    return '<div class="reward-card">'
      + '<div class="reward-icon">🎁</div>'
      + '<div class="reward-info"><div class="reward-name">' + escapeHtml(reward.name) + '</div><div class="reward-desc">' + escapeHtml(reward.category) + ' · Stock ' + reward.stock + '</div></div>'
      + '<div class="reward-actions"><div class="reward-cost">' + reward.cost + ' pts</div>'
      + (reward.can_redeem
        ? '<button class="btn-sm primary" onclick="redeemReward(' + reward.reward_id + ')">Redeem</button>'
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

async function redeemReward(rewardId) {
  var formData = new FormData();
  formData.append('reward_id', rewardId);
  var reward = rewardsCache.find(function(item) { return item.reward_id === rewardId; });
  var rewardName = reward ? reward.name : 'Reward';

  try {
    await apiPost('api/rewards.php', formData);
    showToast('🎉 ' + rewardName + ' redeemed!');
    loadRewards();
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

document.addEventListener('DOMContentLoaded', initRiderRewards);
