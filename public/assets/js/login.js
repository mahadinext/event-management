// document.getElementById('loginForm').addEventListener('submit', function(e) {
$("#loginForm").on('submit', function(e) {
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    
    // Optional: Add loading state
    submitButton.innerHTML = 'Logging in...';
});