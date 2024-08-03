// public/js/confirm-delete.js
function openConfirmDeleteModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
    } else {
        console.error(`Modal with ID '${modalId}' not found.`);
    }
}

function closeConfirmDeleteModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    } else {
        console.error(`Modal with ID '${modalId}' not found.`);
    }
}

function submitConfirmDeleteForm(formId) {
    var form = document.getElementById(formId);
    if (form) {
        form.submit();
    } else {
        console.error(`Form with ID '${formId}' not found.`);
    }
}
