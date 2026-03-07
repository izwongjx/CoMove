/* Page script: dashboard (driver) */
function initDriverDashboard() {
    const offerRideBtn = document.querySelector('.offerRideButton button');
    const modal = document.getElementById('offerRideModal');
    const close = modal.querySelector('.close');

    modal.style.display = 'none';
    
    //open
    offerRideBtn.addEventListener('click', () => {
    modal.style.display = 'flex';
    });

    // close 
    close.addEventListener('click', () => {
    modal.style.display = 'none';
    });

    // close when press outside
    window.addEventListener('click', (e) => {
    if (e.target === modal) {
        modal.style.display = 'none';
    }
    });
}

initDriverDashboard();

