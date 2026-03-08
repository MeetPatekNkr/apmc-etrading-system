// Countdown timers for auction listings
function updateCountdowns() {
    document.querySelectorAll('[id^="countdown-"]').forEach(el => {
        const endTime = new Date(el.dataset.end).getTime();
        const now = new Date().getTime();
        const diff = endTime - now;

        if (diff <= 0) {
            el.textContent = '⏱ Ended';
            el.style.color = '#6b7280';
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        if (days > 0) {
            el.textContent = `⏱ ${days}d ${hours}h ${minutes}m`;
        } else if (hours > 0) {
            el.textContent = `⏱ ${hours}h ${minutes}m ${seconds}s`;
            el.style.color = '#d97706';
        } else {
            el.textContent = `⏱ ${minutes}m ${seconds}s`;
            el.style.color = '#dc2626';
        }
    });
}

updateCountdowns();
setInterval(updateCountdowns, 1000);
