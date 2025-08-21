window.addEventListener('load', hideLoader);

function hideLoader() {
    const el = document.getElementById('app-loader');
    if (!el) return;
    el.classList.add('is-hidden');
    // Retire du DOM après la transition (optionnel)
    setTimeout(() => el.remove(), 300);
}