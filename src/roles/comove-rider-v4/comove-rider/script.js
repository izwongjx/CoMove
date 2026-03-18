/* ============================================
   COMOVE – Global Utility Script
   public-assets/script.js
   ============================================ */

/* ---- Toast ---- */
let _toastTimer;
function showToast(msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.classList.remove('show'), 3000);
}

async function apiGet(url) {
  const res = await fetch(url, { credentials: 'same-origin' });
  const data = await res.json();
  if (!res.ok || !data.ok) {
    throw new Error((data && data.message) || 'Request failed');
  }
  return data.data;
}

async function apiPost(url, formData) {
  const res = await fetch(url, {
    method: 'POST',
    body: formData,
    credentials: 'same-origin'
  });
  const data = await res.json();
  if (!res.ok || !data.ok) {
    throw new Error((data && data.message) || 'Request failed');
  }
  return data.data;
}

function escapeHtml(value) {
  return String(value == null ? '' : value).replace(/[&<>\"']/g, function(ch) {
    return {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    }[ch];
  });
}

// Rider-only access guard. Shared here so admin status changes immediately
// apply across rider pages without relying on temporary client-side data.
(function enforceRiderAccess() {
  async function verifyRiderSession() {
    try {
      const response = await fetch('../../../auth/session-status.php?role=rider', {
        credentials: 'same-origin',
        cache: 'no-store'
      });
      const payload = await response.json();

      if (!response.ok || !payload.ok || !payload.authenticated || !payload.active) {
        const reason = payload && payload.message ? payload.message : 'Please log in as an active rider.';
        window.alert(reason);
        window.location.href = '../../../auth/login/login.html';
      }
    } catch (error) {
      window.alert('Unable to verify rider access right now. Please log in again.');
      window.location.href = '../../../auth/login/login.html';
    }
  }

  verifyRiderSession();
})();
