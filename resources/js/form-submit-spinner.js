import { setButtonLoading } from './button-spinner.js';

/**
 * Applies to every <form> in the application automatically: the browser
 * only dispatches 'submit' once native validation (required fields, etc.)
 * has already passed, so by the time this runs the request is really
 * going out. `event.submitter` is the exact button that triggered it, so
 * this works even on pages with more than one submit button per form.
 */
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const button = event.submitter ?? form.querySelector('button[type="submit"]');

    if (button instanceof HTMLButtonElement && !button.disabled) {
        setButtonLoading(button, true);
    }
});
