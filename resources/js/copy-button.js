/**
 * Any element with class="copy-button" and a data-copy-value attribute
 * copies that value to the clipboard when clicked — used for long values
 * like a blockchain commitment or transaction hash that are shown
 * truncated on screen but need to be copyable in full. Uses event
 * delegation so it works on buttons added to any page without each page
 * needing its own script.
 */
document.addEventListener('click', async (event) => {
    const button = event.target.closest('.copy-button');

    if (!button || !button.dataset.copyValue) {
        return;
    }

    try {
        await navigator.clipboard.writeText(button.dataset.copyValue);
    } catch (error) {
        return;
    }

    const icon = button.querySelector('i');
    if (!icon) {
        return;
    }

    const originalClass = icon.className;
    icon.className = 'bi bi-check-lg text-success copy-pop';
    setTimeout(() => {
        icon.className = originalClass;
    }, 1200);
});
