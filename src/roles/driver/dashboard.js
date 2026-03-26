// for the offer ride popo up
function initDriverDashboard() {
    const offerRideBtn = document.querySelector('.offerRideButton button');
    const modal = document.getElementById('offerRideModal');
    const close = modal.querySelector('.close');

    offerRideBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    close.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
}

initDriverDashboard();

// for the uplaod proof
document.querySelectorAll('input[type="file"]').forEach(function(input) {
    input.addEventListener('change', function() {
        const form = this.closest('form');
        const formData = new FormData(form);
        const btn = form.querySelector('button[type="button"]');

        btn.textContent = 'UPLOADING...';
        btn.disabled = true;

        fetch('upload_proof.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            if (result.trim() === 'success') {
                // show green tick next to button
                btn.textContent = '✓ UPLOADED';
                btn.style.backgroundColor = '#c4f547';
                btn.style.color = 'black';
                btn.disabled = true;
            } else {
                btn.textContent = 'FAILED - TRY AGAIN';
                btn.style.backgroundColor = '#f87171';
                btn.disabled = false;
            }
        });
    });
});