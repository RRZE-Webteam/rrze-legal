/**
 * Open the dashboard confirmation in the browser's modal layer. If that layer
 * is unavailable, the server-rendered form remains the only visible content.
 *
 * @returns {void}
 */
function openRequiredFieldsDialog() {
    const dialog = document.getElementById("rrze-legal-required-dialog");
    if (!dialog || typeof dialog.showModal !== "function") {
        return;
    }

    dialog.addEventListener("cancel", preventDialogDismissal);
    try {
        dialog.close();
        dialog.showModal();
    } catch {
        dialog.setAttribute("open", "");
        return;
    }

    document.body.classList.add("rrze-legal-ack-modal");
    document.getElementById("rrze-legal-dialog-title").focus();
}

/**
 * Keep the mandatory confirmation open when the browser receives Escape.
 *
 * @param {Event} event Cancelable close request from the native dialog.
 * @returns {void}
 */
function preventDialogDismissal(event) {
    event.preventDefault();
}

openRequiredFieldsDialog();
