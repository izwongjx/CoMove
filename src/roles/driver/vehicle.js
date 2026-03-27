function initDriverVehicle() {
  const btn   = document.getElementById('updateVehicleBtn');
  const modal = document.getElementById('updateVehicleModal');
  const closeBtn = modal.querySelector('.close');

  // clicking the button opens the modal
  btn.addEventListener('click', function () {
    modal.style.display = 'flex';
  });

  // clicking × closes it
  closeBtn.addEventListener('click', function () {
    modal.style.display = 'none';
  });

  // clicking outside the modal box also closes it
  window.addEventListener('click', function (e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
}

initDriverVehicle();