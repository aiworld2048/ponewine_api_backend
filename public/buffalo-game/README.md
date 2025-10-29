# Buffalo Game Frontend Integration

A complete HTML/CSS/JavaScript frontend for integrating Buffalo Game with your Laravel backend API.

## 📁 File Structure

```
public/buffalo-game/
├── index.html          # Login page
├── lobby.html          # Game lobby with room selection
├── game.html           # Game player page
├── css/
│   └── style.css       # All styles
├── js/
│   ├── config.js       # API configuration
│   ├── auth.js         # Authentication helpers
│   ├── api.js          # API request helpers
│   ├── login.js        # Login page logic
│   ├── lobby.js        # Lobby page logic
│   └── game.js         # Game page logic
└── README.md           # This file
```

## 🚀 Features

- ✅ User authentication (login/logout)
- ✅ Display available game rooms based on user balance
- ✅ Real-time balance updates
- ✅ Responsive design (mobile & desktop)
- ✅ Room-based access control
- ✅ Game launch integration
- ✅ Clean, modern UI

## 🎮 Game Rooms

1. **Room 1**: 50 MMK minimum bet (Low)
2. **Room 2**: 500 MMK minimum bet (Medium)
3. **Room 3**: 5,000 MMK minimum bet (High)
4. **Room 4**: 10,000 MMK minimum bet (VIP)

## 🔧 Configuration

### 1. Update API Base URL

Edit `js/config.js`:

```javascript
const API_CONFIG = {
    BASE_URL: 'https://your-domain.com/api',  // Update this
    // ... rest of config
};
```

### 2. Update Provider ID (if different)

In `js/config.js`:

```javascript
BUFFALO_PROVIDER_ID: 23,  // Update if your provider ID is different
```

## 📱 Usage

### Access the Frontend

1. **Login Page**: `https://your-domain.com/buffalo-game/index.html`
2. **Direct Lobby** (requires login): `https://your-domain.com/buffalo-game/lobby.html`

### Test Accounts

Default password: **buffalovip**

- **Owner**: Username: `O`
- **Agent**: Username: `AG1`, `AG2`, `AG3`
- **Player**: Username: `PLAYER0101` - `PLAYER0304`, `SKP0101`

## 🎯 User Flow

1. **Login** → User enters credentials
2. **Lobby** → Shows available rooms based on balance
3. **Room Selection** → User clicks "Play Now" on available room
4. **Game Launch** → Game loads in iframe
5. **Play** → User plays the game, balance updates automatically
6. **Back to Lobby** → User can return to select different room

## 🔐 Security Features

- Token-based authentication
- Protected API endpoints
- Session management
- Automatic logout on token expiry

## 🎨 Customization

### Change Colors

Edit `css/style.css`:

```css
/* Primary gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Update to your brand colors */
background: linear-gradient(135deg, #YOUR_COLOR_1 0%, #YOUR_COLOR_2 100%);
```

### Add Logo

Logo is already configured using `img/logo.png`. To change:

1. Replace the file: `public/buffalo-game/img/logo.png`
2. The logo appears on both login page and lobby header

## 📝 API Endpoints Used

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout

### Buffalo Game
- `GET /api/buffalo/game-auth` - Get game authentication data
- `POST /api/buffalo/game-url` - Get game URL
- `POST /api/buffalo/launch-game` - Launch game

## 🐛 Troubleshooting

### "Failed to load game data"
- Check API_CONFIG.BASE_URL in config.js
- Verify backend API is running
- Check browser console for errors

### "Token verification failed"
- Clear browser localStorage
- Login again

### Game not loading
- Check if Buffalo provider ID matches your database
- Verify game URL is correct in backend
- Check browser console for CORS errors

## 🚀 Deployment

### 1. Copy Files

```bash
# Copy frontend files to your public directory
cp -r public/buffalo-game /path/to/your/laravel/public/
```

### 2. Update Config

Update `js/config.js` with your production API URL

### 3. Test

1. Access: `https://your-domain.com/buffalo-game/`
2. Login with test account
3. Verify rooms display correctly
4. Test game launch

## 📱 Mobile Support

The frontend is fully responsive and works on:
- 📱 Mobile phones
- 📱 Tablets
- 💻 Desktop computers

## 🎯 Production Checklist

- [ ] Update API_CONFIG.BASE_URL in config.js
- [ ] Update provider ID if different
- [ ] Add your logo
- [ ] Customize colors to match brand
- [ ] Test all functionality
- [ ] Enable HTTPS
- [ ] Test on mobile devices
- [ ] Configure CORS properly
- [ ] Set up error logging

## 💡 Tips

1. **Balance Updates**: Balance refreshes every 10 seconds during gameplay
2. **Room Access**: Rooms automatically lock/unlock based on balance
3. **Session**: Users stay logged in until they logout or token expires
4. **Mobile**: Swipe-friendly interface for mobile users

## 🆘 Support

For issues or questions:
1. Check browser console for errors
2. Verify API endpoints are working
3. Check Laravel logs for backend errors

---

**Enjoy your Buffalo Game! 🎰🎉**

