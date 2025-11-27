# Buffalo Game Lobby URL Configuration Update

## 📋 Overview

Updated the Buffalo game integration to correctly use the provider's game server URL (`https://prime.next-api.net`) as the `lobbyUrl` parameter in the Game Login API request.

---

## 🔄 Changes Made

### 1. **Updated Configuration**
   - **File:** `config/buffalo.php`
   - **Added:** `game_server_url` configuration
   - **Default:** `https://prime.next-api.net`

### 2. **Updated Service Method**
   - **File:** `app/Services/BuffaloGameService.php`
   - **Method:** `getGameUrlFromProvider()`
   - **Change:** Now uses `https://prime.next-api.net` as `lobbyUrl` in API request

---

## 📡 API Request Format

### **Game Login API Request**

**Endpoint:** `https://api-ms3.african-buffalo.club/api/game-login`

**Request Payload:**
```json
{
    "uid": "testtest13333",
    "domain": "prime.com",
    "lobbyUrl": "https://prime.next-api.net",  // ← Provider's game server URL
    "roomId": "1",
    "token": "dc98b4ebf5e3c4d7aced87614a0a34505117b0a9696b22ebb67aebc5c6282f04",
    "gameId": 23
}
```

**Key Points:**
- `lobbyUrl` = `https://prime.next-api.net` (game server URL)
- `roomId` = string format (`"1"`, `"2"`, etc.)
- `gameId` = 23 (normal) or 42 (scatter)

---

## 📥 API Response Format

### **Expected Response**

The provider API returns a game URL in this format:

```
https://prime.next-api.net/?gameId=42&roomId=1&uid=9789d102-de7c-4d07-beb6-34f05bee7e83&token=dc98b4ebf5e3c4d7aced87614a0a34505117b0a9696b22ebb67aebc5c6282f04&lobbyUrl=https%3A%2F%2Fbuffaloking788.com
```

**URL Breakdown:**
- **Base URL:** `https://prime.next-api.net` (game server)
- **Query Parameters:**
  - `gameId=42` (game type)
  - `roomId=1` (room selection)
  - `uid=...` (user identifier)
  - `token=...` (authentication token)
  - `lobbyUrl=https%3A%2F%2Fbuffaloking788.com` (client's website URL for redirect)

**Note:** The `lobbyUrl` query parameter in the returned URL contains the **client's website URL** (URL-encoded), which is where players will be redirected when they exit the game.

---

## ⚙️ Configuration

### **Environment Variables**

Update your `.env` file:

```env
# Buffalo Game Provider Configuration
BUFFALO_API_URL=https://api-ms3.african-buffalo.club/api/game-login
BUFFALO_DOMAIN=prime.com
BUFFALO_GAME_SERVER_URL=https://prime.next-api.net  # Provider's game server
BUFFALO_GAME_ID=23

# Client's website URL (for redirect on exit)
BUFFALO_SITE_URL=https://maxwinmyanmar.pro
```

---

## 🔍 How It Works

### **Flow:**

1. **Client calls your API:**
   ```http
   POST /api/buffalo/launch-game
   {
     "game_id": 42,
     "room_id": 1
   }
   ```

2. **Your service calls provider API:**
   ```json
   POST https://api-ms3.african-buffalo.club/api/game-login
   {
     "uid": "...",
     "token": "...",
     "gameId": 42,
     "roomId": "1",
     "lobbyUrl": "https://prime.next-api.net",  // Game server
     "domain": "prime.com"
   }
   ```

3. **Provider returns game URL:**
   ```
   https://prime.next-api.net/?gameId=42&roomId=1&uid=...&token=...&lobbyUrl=https%3A%2F%2Fbuffaloking788.com
   ```

4. **Your API returns to client:**
   ```json
   {
     "code": 1,
     "msg": "Game launched successfully",
     "Url": "https://prime.next-api.net/?gameId=42&roomId=1&uid=...&token=...&lobbyUrl=...",
     "game_url": "..."
   }
   ```

---

## ✅ Verification

### **Check API Request**

Verify in logs that the API request includes:
- `lobbyUrl: "https://prime.next-api.net"` (game server)
- `roomId: "1"` (as string)

### **Check API Response**

Verify the returned URL:
- Starts with `https://prime.next-api.net`
- Contains all required query parameters
- Has `lobbyUrl` query parameter with client's website URL

---

## 📝 Important Notes

1. **Two Different `lobbyUrl` Values:**
   - **In API Request:** `https://prime.next-api.net` (game server URL)
   - **In Returned URL Query:** Client's website URL (e.g., `https://buffaloking788.com`)

2. **Client Website URL:**
   - The provider determines the client's website URL (possibly from `domain` parameter or pre-configured)
   - This URL is included in the returned game URL's query parameter
   - Players are redirected here when they exit the game

3. **Game Server URL:**
   - Always `https://prime.next-api.net` for the provider
   - This is where the game loads from
   - Used as `lobbyUrl` in the API request

---

## 🧪 Testing

### **Test Normal Buffalo (gameId: 23)**
```bash
POST /api/buffalo/launch-game
{
  "game_id": 23,
  "room_id": 1
}
```

**Expected Response URL:**
```
https://prime.next-api.net/?gameId=23&roomId=1&uid=...&token=...&lobbyUrl=...
```

### **Test Scatter Buffalo (gameId: 42)**
```bash
POST /api/buffalo/launch-game
{
  "game_id": 42,
  "room_id": 1
}
```

**Expected Response URL:**
```
https://prime.next-api.net/?gameId=42&roomId=1&uid=...&token=...&lobbyUrl=...
```

---

## 🔧 Code Changes Summary

1. **Config:** Added `game_server_url` setting
2. **Service:** Updated to use game server URL as `lobbyUrl` in API request
3. **Logging:** Enhanced to verify returned URL format
4. **Documentation:** Added comments explaining the two `lobbyUrl` values

---

## ✅ Status

- ✅ API request uses `https://prime.next-api.net` as `lobbyUrl`
- ✅ Configuration added for game server URL
- ✅ Code updated to match provider requirements
- ✅ Logging enhanced for verification

The integration now correctly sends the game server URL as `lobbyUrl` in the API request, and the provider returns a properly formatted game URL with the client's website URL in the query parameter.

