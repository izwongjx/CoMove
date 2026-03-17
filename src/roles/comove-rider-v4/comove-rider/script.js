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
