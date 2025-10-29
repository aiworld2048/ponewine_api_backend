// Game Page Logic

// Require authentication
if (!requireAuth()) {
    // Will redirect to login
}

// Get game URL from query parameters
const urlParams = new URLSearchParams(window.location.search);
const gameUrl = urlParams.get('url');

if (!gameUrl) {
    alert('No game URL provided');
    window.location.href = 'lobby.html';
}

// Get user and room info
const user = getUser();
const selectedRoom = JSON.parse(localStorage.getItem(STORAGE_KEYS.SELECTED_ROOM) || '{}');

// Initialize game
function initializeGame() {
    if (!user) {
        window.location.href = 'index.html';
        return;
    }
    
    // Set up game header
    document.getElementById('playerName').textContent = user.user_name || user.name;
    document.getElementById('gameBalance').textContent = formatCurrency(user.balance || 0);
    
    if (selectedRoom.roomInfo) {
        document.getElementById('roomInfo').textContent = selectedRoom.roomInfo.name;
    }
    
    // Load game in iframe
    const gameFrame = document.getElementById('gameFrame');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const gameHeader = document.getElementById('gameHeader');
    const loadingText = loadingOverlay.querySelector('.loading-text');
    
    let isGameLoaded = false;
    let loadStartTime = Date.now();
    
    // Show elapsed time during loading
    const timeUpdateInterval = setInterval(() => {
        if (!isGameLoaded) {
            const elapsed = Math.floor((Date.now() - loadStartTime) / 1000);
            loadingText.textContent = `Loading Buffalo Game... (${elapsed}s)`;
        }
    }, 1000);
    
    // Decode game URL
    let finalGameUrl = decodeURIComponent(gameUrl);
    
    console.log('Original game URL:', finalGameUrl);
    
    // For HTTPS sites (like Vercel), use backend proxy
    // Public CORS proxies don't work because they block iframe embedding
    if (window.location.protocol === 'https:' && finalGameUrl.startsWith('http://')) {
        // Use your Laravel backend proxy (REQUIRED for Vercel HTTPS)
        // Auto-detect the API URL from config or use current domain
        const apiBase = API_CONFIG?.BASE_URL || window.location.origin + '/api';
        const backendProxy = apiBase.replace('/api', '') + '/api/buffalo/proxy-game?url=';
        finalGameUrl = backendProxy + encodeURIComponent(finalGameUrl);
        
        console.log('Using backend proxy for HTTPS compatibility');
        console.log('Proxy URL:', backendProxy);
    }
    
    console.log('Loading game URL:', finalGameUrl);
    
    // Set iframe source
    gameFrame.src = finalGameUrl;
    
    // Show game after 3 seconds regardless of load status (like other sites)
    // This allows the game to continue loading in background while visible
    const forceShowTimeout = setTimeout(() => {
        if (!isGameLoaded) {
            console.log('Showing game after timeout (game may still be loading resources)');
            hideLoadingAndShowGame();
        }
    }, 3000);
    
    // Also try to detect when iframe document is ready (not all resources)
    const checkIframeReady = setInterval(() => {
        try {
            // Check if iframe document exists and has started loading
            if (gameFrame.contentWindow && gameFrame.contentWindow.document) {
                const iframeDoc = gameFrame.contentWindow.document;
                
                // If document has started loading (has HTML element)
                if (iframeDoc.documentElement && iframeDoc.readyState !== 'complete') {
                    console.log('Iframe document detected, readyState:', iframeDoc.readyState);
                }
                
                // Show game when document is interactive or complete
                if (iframeDoc.readyState === 'interactive' || iframeDoc.readyState === 'complete') {
                    clearInterval(checkIframeReady);
                    clearTimeout(forceShowTimeout);
                    console.log('Iframe document ready, showing game');
                    hideLoadingAndShowGame();
                }
            }
        } catch (e) {
            // Cross-origin iframe - can't access contentWindow
            // This is normal for external game servers, rely on timeout instead
            console.log('Cross-origin iframe detected, using timeout method');
            clearInterval(checkIframeReady);
        }
    }, 200);
    
    // Stop checking after 5 seconds
    setTimeout(() => {
        clearInterval(checkIframeReady);
    }, 5000);
    
    // Traditional onload as backup (fires when ALL resources loaded)
    gameFrame.onload = function() {
        if (!isGameLoaded) {
            console.log('Iframe fully loaded (all resources)');
            hideLoadingAndShowGame();
        }
    };
    
    // Handle load error
    gameFrame.onerror = function() {
        isGameLoaded = true;
        clearTimeout(forceShowTimeout);
        clearInterval(checkIframeReady);
        clearInterval(timeUpdateInterval);
        alert('Failed to load game. Please try again.');
        window.location.href = 'lobby.html';
    };
    
    // Function to hide loading and show game
    function hideLoadingAndShowGame() {
        if (isGameLoaded) return; // Already shown
        
        isGameLoaded = true;
        clearTimeout(forceShowTimeout);
        clearInterval(checkIframeReady);
        clearInterval(timeUpdateInterval);
        
        // Log loading time for debugging
        const loadTime = ((Date.now() - loadStartTime) / 1000).toFixed(2);
        console.log(`Game shown after ${loadTime} seconds`);
        
        // Hide loading and show game
        loadingOverlay.style.display = 'none';
        gameFrame.style.display = 'block';
        gameHeader.style.display = 'flex';
    }
    
    // Start balance update interval
    startBalanceUpdate();
}

// Update balance periodically
let balanceUpdateInterval;

function startBalanceUpdate() {
    // Update balance every 10 seconds
    balanceUpdateInterval = setInterval(async () => {
        try {
            const response = await getBuffaloGameAuth();
            if (response.code === 1 && response.data) {
                const newBalance = response.data.user_balance || 0;
                document.getElementById('gameBalance').textContent = formatCurrency(newBalance);
                
                // Update stored user data
                const currentUser = getUser();
                if (currentUser) {
                    currentUser.balance = newBalance;
                    saveUser(currentUser);
                }
            }
        } catch (error) {
            console.error('Error updating balance:', error);
        }
    }, 10000);
}

// Stop balance updates when leaving page
window.addEventListener('beforeunload', () => {
    if (balanceUpdateInterval) {
        clearInterval(balanceUpdateInterval);
    }
});

// Back to lobby
function backToLobby() {
    if (confirm('Are you sure you want to leave the game?')) {
        if (balanceUpdateInterval) {
            clearInterval(balanceUpdateInterval);
        }
        window.location.href = 'lobby.html';
    }
}

// Handle iframe communication (optional)
window.addEventListener('message', (event) => {
    // Handle messages from game iframe if needed
    console.log('Message from game:', event.data);
    
    // Example: Handle game events
    if (event.data.type === 'balance_update') {
        document.getElementById('gameBalance').textContent = formatCurrency(event.data.balance);
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeGame);

// Prevent accidental navigation (browser will show native confirmation)
window.addEventListener('beforeunload', (event) => {
    // Browser will show its own confirmation dialog
    event.preventDefault();
    event.returnValue = ''; // Required for Chrome
    return ''; // Required for some older browsers
});

