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
