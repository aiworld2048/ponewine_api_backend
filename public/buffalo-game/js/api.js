// API Helper Functions

/**
 * Make API request
 */
async function apiRequest(endpoint, options = {}) {
    const url = API_CONFIG.BASE_URL + endpoint;
    
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    };
    
    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers,
        },
    };
    
    try {
        const response = await fetch(url, mergedOptions);
        const data = await response.json();
        
        // Check if response is successful
        if (!response.ok) {
            throw new Error(data.msg || data.message || 'Request failed');
        }
        
        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

/**
 * Login API call
 */
async function loginAPI(username, password) {
    return await apiRequest(API_CONFIG.ENDPOINTS.LOGIN, {
        method: 'POST',
        body: JSON.stringify({
            user_name: username,
            password: password,
        }),
    });
}

/**
 * Get Buffalo game authentication data
 */
async function getBuffaloGameAuth() {
    return await apiRequest(API_CONFIG.ENDPOINTS.BUFFALO_GAME_AUTH, {
        method: 'GET',
        headers: getAuthHeaders(),
    });
}

/**
 * Get Buffalo game URL
 */
async function getBuffaloGameUrl(roomId, lobbyUrl = null) {
    const body = {
        room_id: roomId,
    };
    
    if (lobbyUrl) {
        body.lobby_url = lobbyUrl;
    }
    
    return await apiRequest(API_CONFIG.ENDPOINTS.BUFFALO_GAME_URL, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(body),
    });
}

/**
 * Launch Buffalo game (compatible with existing frontend)
 */
async function launchBuffaloGame(roomId) {
    return await apiRequest(API_CONFIG.ENDPOINTS.BUFFALO_LAUNCH_GAME, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({
            type_id: API_CONFIG.BUFFALO_TYPE_ID,
            provider_id: API_CONFIG.BUFFALO_PROVIDER_ID,
            game_id: API_CONFIG.BUFFALO_GAME_ID,
            room_id: roomId,
        }),
    });
}

/**
 * Get available rooms based on user balance
 */
function getAvailableRooms(userBalance) {
    const availableRooms = {};
    
    for (const [roomId, config] of Object.entries(ROOM_CONFIG)) {
        if (userBalance >= config.min_bet) {
            availableRooms[roomId] = config;
        }
    }
    
    return availableRooms;
}

