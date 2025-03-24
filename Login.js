document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById("login-form");
    const passwordInput = document.getElementById("password");
    const togglePasswordBtn = document.getElementById("toggle-password");
    const emailInput = document.getElementById("email");

    // Password toggle functionality
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
                this.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
                this.setAttribute('aria-label', 'Show password');
            }
        });
    }

    // Form submission validation
    loginForm.addEventListener("submit", function(event) {
        let isValid = true;
        let email = emailInput.value.trim();
        let password = passwordInput.value.trim();

        // Email validation (must follow standard format like example@gmail.com)
        let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!email.match(emailRegex)) {
            document.getElementById("email-error").style.display = "block";
            isValid = false;
        } else {
            document.getElementById("email-error").style.display = "none";
        }

        // Password validation (minimum 6 characters)
        if (password.length < 6) {
            document.getElementById("password-error").style.display = "block";
            isValid = false;
        } else {
            document.getElementById("password-error").style.display = "none";
        }

        if (!isValid) {
            event.preventDefault(); // Prevent form submission if validation fails
        }
    });
});