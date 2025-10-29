// Lobby Page Logic

// Require authentication
if (!requireAuth()) {
    // Will redirect to login
}

let userBalance = 0;

// Initialize lobby
async function initializeLobby() {
    const user = getUser();
    
    if (!user) {
        window.location.href = 'index.html';
        return;
    }
    
    // Display user info
    document.getElementById('userName').textContent = user.user_name || user.name;
    
    try {
        // Get game authentication data (includes balance and available rooms)
        const response = await getBuffaloGameAuth();
        
        console.log('Game auth response:', response);
        
        if (response.data) {
            userBalance = response.data.user_balance || 0;
            
            // Update balance display
            document.getElementById('userBalance').textContent = formatCurrency(userBalance);
            
            // Get available rooms
            const availableRooms = response.data.available_rooms || getAvailableRooms(userBalance);
            
            // Display rooms
            displayRooms(availableRooms);
        } else {
            throw new Error(response.msg || 'Failed to load game data');
        }
    } catch (error) {
        console.error('Error loading lobby:', error);
        
        // Show error and fallback to basic room display
        showToast('Error loading game data. Please refresh the page.', 'error');
        displayRooms(getAvailableRooms(0)); // Show all rooms as locked
    }
}

// Display rooms
function displayRooms(availableRooms) {
    const roomsGrid = document.getElementById('roomsGrid');
    roomsGrid.innerHTML = '';
    
    // Display all rooms
    for (const [roomId, config] of Object.entries(ROOM_CONFIG)) {
        const isAvailable = availableRooms.hasOwnProperty(roomId);
        const roomCard = createRoomCard(roomId, config, isAvailable);
        roomsGrid.appendChild(roomCard);
    }
}

// Create room card element
function createRoomCard(roomId, config, isAvailable) {
    const card = document.createElement('div');
    card.className = `room-card ${!isAvailable ? 'disabled' : ''}`;
    
    card.innerHTML = `
        <img src="img/af.png" alt="${config.name}" style="width: 100px; height: 100px; object-fit: contain; margin-bottom: 15px;">
        <span class="room-level ${config.color}">${config.level}</span>
        <h3 class="room-name">${config.name}</h3>
        <p class="room-bet">Min Bet: <strong>${formatCurrency(config.min_bet)}</strong> MMK</p>
        <button class="btn-play" ${!isAvailable ? 'disabled' : ''} onclick="launchGame(${roomId})">
            ${isAvailable ? 'Play Now' : 'Locked'}
        </button>
        ${!isAvailable ? '<p class="locked-message">Insufficient balance</p>' : ''}
    `;
    
    return card;
}

// Launch game
async function launchGame(roomId) {
    const modal = document.getElementById('gameModal');
    const launchMessage = document.getElementById('launchMessage');
    
    // Show modal
    modal.classList.add('show');
    launchMessage.textContent = 'Preparing your game...';
    
    try {
        // Call launch game API
        const response = await launchBuffaloGame(roomId);
        
        console.log('Launch game response:', response);
        
        if (response.game_url || (response.data && response.data.game_url)) {
            launchMessage.textContent = 'Launching game...';
            
            // Save selected room
            localStorage.setItem(STORAGE_KEYS.SELECTED_ROOM, JSON.stringify({
                roomId: roomId,
                roomInfo: ROOM_CONFIG[roomId],
            }));
            
            // Get game URL from response
            const gameUrl = response.game_url || response.data?.game_url || response.Url;
            
            if (gameUrl) {
                // Redirect to game page with URL parameter
                const encodedUrl = encodeURIComponent(gameUrl);
                window.location.href = `game.html?url=${encodedUrl}`;
            } else {
                throw new Error('No game URL in response');
            }
        } else {
            throw new Error(response.message || response.msg || 'Failed to launch game');
        }
    } catch (error) {
        console.error('Error launching game:', error);
        launchMessage.textContent = 'Error: ' + error.message;
        
        setTimeout(() => {
            closeGameModal();
        }, 2000);
    }
}

// Close game modal
function closeGameModal() {
    const modal = document.getElementById('gameModal');
    modal.classList.remove('show');
}

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('gameModal');
    if (event.target === modal) {
        closeGameModal();
    }
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeLobby);

