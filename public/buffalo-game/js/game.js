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
    
    // Set iframe source
    gameFrame.src = decodeURIComponent(gameUrl);
    
    // Show frame and header when loaded
    gameFrame.onload = function() {
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
            gameFrame.style.display = 'block';
            gameHeader.style.display = 'flex';
        }, 1000);
    };
    
    // Handle load error
    gameFrame.onerror = function() {
        alert('Failed to load game. Please try again.');
        window.location.href = 'lobby.html';
    };
    
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

// Prevent accidental navigation
window.addEventListener('beforeunload', (event) => {
    if (confirm('Are you sure you want to leave the game?')) {
        return;
    }
    event.preventDefault();
    event.returnValue = '';
});

