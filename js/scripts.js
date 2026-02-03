document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const errorMessage = document.querySelector('.error');

    form.addEventListener('submit', function(event) {
        if (usernameInput.value.trim() === '' || passwordInput.value.trim() === '') {
            event.preventDefault();
            errorMessage.textContent = 'Both fields are required.';
        }
    });

    usernameInput.addEventListener('input', function() {
        errorMessage.textContent = '';
    });

    passwordInput.addEventListener('input', function() {
        errorMessage.textContent = '';
    });

    const alertOverlay = document.querySelector('.alert-overlay');
    const alertBox = document.querySelector('.alert-box');
    const alertButton = document.querySelector('.alert-box button');

    if (alertOverlay) {
        alertButton.addEventListener('click', function() {
            alertOverlay.style.display = 'none';
        });
    }
});