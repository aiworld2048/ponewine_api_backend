// Authentication Helper Functions

/**
 * Save authentication token
 */
function saveToken(token) {
    localStorage.setItem(STORAGE_KEYS.TOKEN, token);
}

/**
 * Get authentication token
 */
function getToken() {
    return localStorage.getItem(STORAGE_KEYS.TOKEN);
}

/**
 * Save user data
 */
function saveUser(user) {
    localStorage.setItem(STORAGE_KEYS.USER, JSON.stringify(user));
}

/**
 * Get user data
 */
function getUser() {
    const userData = localStorage.getItem(STORAGE_KEYS.USER);
    return userData ? JSON.parse(userData) : null;
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return !!getToken();
}

/**
 * Clear authentication data
 */
function clearAuth() {
    localStorage.removeItem(STORAGE_KEYS.TOKEN);
    localStorage.removeItem(STORAGE_KEYS.USER);
    localStorage.removeItem(STORAGE_KEYS.SELECTED_ROOM);
}

/**
 * Logout user
 */
async function logout() {
    try {
        const token = getToken();
        if (token) {
            await fetch(API_CONFIG.BASE_URL + API_CONFIG.ENDPOINTS.LOGOUT, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });
        }
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        clearAuth();
        window.location.href = 'index.html';
    }
}

/**
 * Require authentication
 * Redirect to login if not authenticated
 */
function requireAuth() {
    if (!isAuthenticated()) {
        window.location.href = 'index.html';
        return false;
    }
    return true;
}

/**
 * Prevent access if already authenticated
 */
function preventAuthAccess() {
    if (isAuthenticated()) {
        window.location.href = 'lobby.html';
        return false;
    }
    return true;
}

/**
 * Get authorization headers
 */
function getAuthHeaders() {
    const token = getToken();
    return {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };
}

