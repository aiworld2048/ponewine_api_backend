// Login Page Logic

// Prevent access if already logged in
preventAuthAccess();

// Handle login form submission
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const spinner = submitBtn.querySelector('.spinner');
    const errorMessage = document.getElementById('errorMessage');
    
    // Hide error message
    errorMessage.style.display = 'none';
    
    // Disable button and show spinner
    submitBtn.disabled = true;
    btnText.textContent = 'Logging in...';
    spinner.style.display = 'inline';
    
    try {
        // Call login API
        const response = await loginAPI(username, password);
        
        console.log('Login response:', response);
        
        // Check if login was successful (Laravel format: response.data contains user info)
        if (response.data && response.data.token) {
            // Save token and user data
            saveToken(response.data.token);
            saveUser(response.data);
            
            // Show success message
            console.log('Login successful!', response.data);
            
            // Redirect to lobby
            window.location.href = 'lobby.html';
        } else {
            throw new Error(response.message || 'Login failed');
        }
    } catch (error) {
        console.error('Login error:', error);
        
        // Show error message
        errorMessage.textContent = error.message || 'Login failed. Please check your credentials.';
        errorMessage.style.display = 'block';
        
        // Re-enable button
        submitBtn.disabled = false;
        btnText.textContent = 'Login';
        spinner.style.display = 'none';
    }
});

// Add enter key support for password field
document.getElementById('password').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        document.getElementById('loginForm').dispatchEvent(new Event('submit'));
    }
});

// Auto-focus username field
document.getElementById('username').focus();

