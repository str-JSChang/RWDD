document.getElementById("signup-form").addEventListener("submit", function(event) {
    // event.preventDefault();

    let isValid = true;
    let username = document.getElementById("username").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();
    let confirmPassword = document.getElementById("confirm-password").value.trim();

    // Username validation (must contain alphabets and at least 6 characters)
    let usernameRegex = /^(?=.*[a-zA-Z])[a-zA-Z0-9]{6,}$/;
    if (!usernameRegex.test(username)) {
        document.getElementById("username-error").textContent = "Username must contain letters and be at least 6 characters.";
        document.getElementById("username-error").style.display = "block";
        isValid = false;
    } else {
        document.getElementById("username-error").style.display = "none";
    }

    // Email validation (must be in proper format)
    let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        document.getElementById("email-error").textContent = "Enter a valid email format (e.g., example@gmail.com).";
        document.getElementById("email-error").style.display = "block";
        isValid = false;
    } else {
        document.getElementById("email-error").style.display = "none";
    }

    // Password validation (minimum 6 characters)
    if (password.length < 6) {
        document.getElementById("password-error").textContent = "Password must be at least 6 characters.";
        document.getElementById("password-error").style.display = "block";
        isValid = false;
    } else {
        document.getElementById("password-error").style.display = "none";
    }

    // Confirm password validation (must match password)
    if (confirmPassword !== password || confirmPassword === "") {
        document.getElementById("confirm-password-error").textContent = "Passwords do not match.";
        document.getElementById("confirm-password-error").style.display = "block";
        isValid = false;
    } else {
        document.getElementById("confirm-password-error").style.display = "none";
    }

    if (isValid) {
        this.submit();
    }
});
