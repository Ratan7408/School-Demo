// assets/js/script.js

document.addEventListener('DOMContentLoaded', function() {
    // Handle edit class button clicks
    document.querySelectorAll('.edit-class').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            row.querySelector('.class-name').classList.add('d-none');
            row.querySelector('.edit-form').classList.remove('d-none');
            this.classList.add('d-none');
        });
    });

    // Handle cancel edit button clicks
    document.querySelectorAll('.cancel-edit').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            row.querySelector('.class-name').classList.remove('d-none');
            row.querySelector('.edit-form').classList.add('d-none');
            row.querySelector('.edit-class').classList.remove('d-none');
        });
    });
});