/**
 * Toggles a button between its normal label and a small inline spinner, so
 * every action that triggers a request — not just the ones with bespoke
 * JavaScript — gives the user visible feedback instead of looking inert
 * while the server responds. The original label is stashed on the element
 * itself so it can be restored exactly (icons included) if the action
 * fails and the user needs to try again.
 */
export function setButtonLoading(button, loading) {
    if (loading) {
        if (button.dataset.originalHtml === undefined) {
            button.dataset.originalHtml = button.innerHTML;
        }

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> '
            + button.dataset.originalHtml;
    } else {
        button.disabled = false;

        if (button.dataset.originalHtml !== undefined) {
            button.innerHTML = button.dataset.originalHtml;
            delete button.dataset.originalHtml;
        }
    }
}

window.setButtonLoading = setButtonLoading;
