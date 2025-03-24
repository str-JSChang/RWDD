document.getElementById("login-form").addEventListener("submit", function(event) {
    // event.preventDefault();

    let isValid = true;
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();

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

    if (isValid) {
        this.submit()
    }
});
