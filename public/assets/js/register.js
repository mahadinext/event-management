document.getElementById('registerForm').addEventListener('submit', function(e) {
    let isValid = true;
    const errors = {};
    
    // First Name validation
    const firstName = document.getElementById('first_name').value.trim();
    if (firstName.length < 2) {
        isValid = false;
        errors.first_name = 'First name must be at least 2 characters';
        showError('first_name', 'First name must be at least 2 characters');
    } else {
        clearError('first_name');
    }
    
    // Last Name validation
    const lastName = document.getElementById('last_name').value.trim();
    if (lastName.length < 2) {
        isValid = false;
        errors.last_name = 'Last name must be at least 2 characters';
        showError('last_name', 'Last name must be at least 2 characters');
    } else {
        clearError('last_name');
    }
    
    // Email validation
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        isValid = false;
        errors.email = 'Please enter a valid email address';
        showError('email', 'Please enter a valid email address');
    } else {
        clearError('email');
    }
    
    // Password validation
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirmation').value;
    
    if (password.length < 8) {
        isValid = false;
        showError('password', 'Password must be at least 8 characters');
    } else if (!/[A-Z]/.test(password)) {
        isValid = false;
        showError('password', 'Password must contain at least one uppercase letter');
    } else if (!/[a-z]/.test(password)) {
        isValid = false;
        showError('password', 'Password must contain at least one lowercase letter');
    } else if (!/[0-9]/.test(password)) {
        isValid = false;
        showError('password', 'Password must contain at least one number');
    } else if (password !== passwordConfirm) {
        isValid = false;
        showError('password', 'Passwords do not match');
    } else {
        clearError('password');
    }
    
    if (!isValid) {
        e.preventDefault();
    } else {
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        
        // Optional: Add loading state
        submitButton.innerHTML = 'Registering...';
    }
});

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.classList.add('is-invalid');
    
    let feedback = field.nextElementSibling.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.appendChild(feedback);
    }
    feedback.textContent = message;
}

function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    field.classList.remove('is-invalid');
    
    const feedback = field.nextElementSibling.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.remove();
    }
}
