// API Configuration
const API_CONFIG = {
    // Update this to your API base URL
    BASE_URL: window.location.origin + '/api',
    
    // API Endpoints
    ENDPOINTS: {
        LOGIN: '/login',
        LOGOUT: '/logout',
        BUFFALO_GAME_AUTH: '/buffalo/game-auth',
        BUFFALO_GAME_URL: '/buffalo/game-url',
        BUFFALO_LAUNCH_GAME: '/buffalo/launch-game',
    },
    
    // Buffalo Game Provider ID (update if different)
    BUFFALO_PROVIDER_ID: 23,
    BUFFALO_TYPE_ID: 1, // Game type ID
    BUFFALO_GAME_ID: 23, // Game ID
};

// Room Configuration (matches backend)
const ROOM_CONFIG = {
    1: { 
        min_bet: 50, 
        name: '50 အခန်း', 
        level: 'Low',
        icon: '🎰',
        color: 'low',
        image: 'img/af.png'
    },
    2: { 
        min_bet: 500, 
        name: '500 အခန်း', 
        level: 'Medium',
        icon: '🎲',
        color: 'medium',
        image: 'img/af.png'
    },
    3: { 
        min_bet: 5000, 
        name: '5000 အခန်း', 
        level: 'High',
        icon: '💎',
        color: 'high',
        image: 'img/af.png'
    },
    4: { 
        min_bet: 10000, 
        name: '10000 အခန်း', 
        level: 'VIP',
        icon: '👑',
        color: 'vip',
        image: 'img/af.png'
    },
};

// Storage Keys
const STORAGE_KEYS = {
    TOKEN: 'buffalo_token',
    USER: 'buffalo_user',
    SELECTED_ROOM: 'buffalo_selected_room',
};

// Helper function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
}

// Helper function to show toast notifications
function showToast(message, type = 'info') {
    // Simple console log for now, you can enhance this with a toast library
    console.log(`[${type.toUpperCase()}] ${message}`);
    alert(message);
}

