// Driver-only access guard. This keeps banned accounts out of static driver pages
// using a live database-backed session check.
(function () {
  async function enforceDriverAccess() {
    try {
      const response = await fetch('../../auth/session-status.php?role=driver', {
        credentials: 'same-origin',
        cache: 'no-store'
      });
      const payload = await response.json();

      if (!response.ok || !payload.ok || !payload.authenticated || !payload.active) {
        const reason = payload && payload.message ? payload.message : 'Please log in as an active driver.';
        window.alert(reason);
        window.location.href = '../../auth/login/login.php';
      }
    } catch (error) {
      window.alert('Unable to verify driver access right now. Please log in again.');
      window.location.href = '../../auth/login/login.php';
    }
  }

  enforceDriverAccess();
})();

