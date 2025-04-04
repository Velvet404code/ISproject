
/*document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');

    signupForm.addEventListener('submit', (event) => {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        // Simple validation
        if (username === '' || email === '' || password === '' || confirmPassword === '') {
            alert('Please fill in all fields.');
            event.preventDefault(); // Stop submission if fields are empty
            return;
        }

        if (password !== confirmPassword) {
            alert('Passwords do not match.');
            event.preventDefault(); // Stop submission if passwords do not match
            return;
        }

        // Remove this line to let the form submit properly
        // window.location.href = 'login.php';
    });
});*/
