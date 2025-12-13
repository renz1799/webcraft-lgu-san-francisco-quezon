import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {

    // -------------------------
    // Status Update
    // -------------------------
    document.querySelectorAll('.js-task-status-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Update task status?',
                text: 'This will update the task and add it to the timeline.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, update',
                cancelButtonText: 'Cancel',
            }).then(result => {
                if (result.isConfirmed) {
                    submitForm(form);
                }
            });
        });
    });

    // -------------------------
    // Comment
    // -------------------------
    document.querySelectorAll('.js-task-comment-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            submitForm(form);
        });
    });

    // -------------------------
    // Shared submit handler
    // -------------------------
    function submitForm(form) {
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(async response => {
            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Request failed');
            }
            return response.json();
        })
        .then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Task updated successfully.',
                timer: 1500,
                showConfirmButton: false,
            }).then(() => {
                window.location.reload();
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Something went wrong.',
            });
        });
    }
});
