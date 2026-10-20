document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const saveSuccess = body.dataset.saveSuccess === '1';
    const homeUrl = body.dataset.homeUrl || '/';

    if (!saveSuccess) {
        const warningModalEl = document.getElementById('adminWarningModal');
        if (warningModalEl) {
            const warningModal = new bootstrap.Modal(warningModalEl);
            warningModal.show();
        }
        return;
    }

    let seconds = 3;
    const countdownEl = document.getElementById('redirectCountdown');

    const interval = setInterval(function () {
        seconds--;

        if (countdownEl && seconds >= 0) {
            countdownEl.textContent = seconds;
        }

        if (seconds <= 0) {
            clearInterval(interval);
            window.location.href = homeUrl;
        }
    }, 1000);
});
