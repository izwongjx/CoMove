/* ============================================
   ECORIDE - Shared JavaScript Utilities
   Pure Vanilla JS - No Frameworks
   ============================================ */


function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
 