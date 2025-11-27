# Buffalo Provider API Update - Based on Official Documentation

## 📋 Overview

Updated the Buffalo game integration to match the **official provider API documentation** with correct endpoint, parameter formats, and game type support.

---

## 🔄 Key Changes

### 1. **Updated API Endpoint**
   - **Old:** `https://staging-ms.african-buffalo.net/api/game-login`
   - **New:** `https://api-ms3.african-buffalo.club/api/game-login`
   - **Config:** Updated in `config/buffalo.php`

### 2. **Fixed Parameter Formats**
   - **`roomId`:** Now sent as **string** (not integer) per provider requirements
   - **`domain`:** Now **required** (not optional)
   - **`gameId`:** Supports both 23 (normal) and 42 (scatter buffalo)

### 3. **Game Type Support**
   - **Normal Buffalo:** `gameId = 23` (default)
   - **Scatter Buffalo:** `gameId = 42`
   - Can be specified in API requests

### 4. **Updated Game Server URLs**
   - **Old:** `http://prime7.wlkfkskakdf.com`
   - **New:** `https://prime.next-api.net` (as shown in provider example)
   - Proxy validation updated to support both

---

## 📡 API Request Format

### **Provider Game Login API**

**Endpoint:** `https://api-ms3.african-buffalo.club/api/game-login`

**Request (JSON):**
```json
{
    "uid": "testtest13333",
    "domain": "prime.com",
    "lobbyUrl": "https://www.google.com",
    "roomId": "1",
    "token": "dc98b4ebf5e3c4d7aced87614a0a34505117b0a9696b22ebb67aebc5c6282f04",
    "gameId": 23
}
```

**Response:**
```json
{
    "url": "https://prime.next-api.net/?gameId=42&roomId=1&uid=...&token=...&lobbyUrl=..."
}
```

---

## ⚙️ Configuration Updates

### **Updated `.env` Variables**

```env
# Buffalo Game Provider Configuration
BUFFALO_API_URL=https://api-ms3.african-buffalo.club/api/game-login
BUFFALO_DOMAIN=prime.com                    # REQUIRED - Get from provider
BUFFALO_GAME_ID=23                          # Default: 23 (normal buffalo)

# Site Configuration
BUFFALO_SITE_NAME=https://maxwinmyanmar.pro
BUFFALO_SITE_PREFIX=mxm
BUFFALO_SITE_URL=https://maxwinmyanmar.pro
```

### **Game Types**

```php
// In config/buffalo.php
'game_types' => [
    'normal' => 23,      // Normal Buffalo game
    'scatter' => 42,     // Scatter Buffalo game
],
```

---

## 🔧 Code Changes

### **1. Config File (`config/buffalo.php`)**
- Updated API URL to `https://api-ms3.african-buffalo.club/api/game-login`
- Removed staging/production split (single URL)
- Added game types configuration
- Made `domain` required with default value

### **2. Service (`BuffaloGameService.php`)**
- **`getGameUrlFromProvider()`:**
  - Sends `roomId` as **string** (not integer)
  - Makes `domain` **required** (always included)
  - Supports `gameId` parameter (23 or 42)
  
- **`generateGameUrl()`:**
  - Added `$gameId` parameter (optional, defaults to 23)

### **3. Controller (`BuffaloGameController.php`)**
- **`generateGameUrl()` method:**
  - Added `game_id` validation (23 or 42)
  - Passes `gameId` to service method
  
- **`launchGame()` method:**
  - Uses `game_id` from request or defaults to 23
  - Supports both normal and scatter buffalo

- **Proxy methods:**
  - Updated validation to support both old and new game server URLs
  - Supports `prime.next-api.net` domain

---

## 🎮 Usage Examples

### **Launch Normal Buffalo (Default)**
```http
POST /api/buffalo/launch-game
Authorization: Bearer {token}
Content-Type: application/json

{
  "type_id": 1,
  "provider_id": 23,
  "game_id": 23,        // Normal buffalo
  "room_id": 2
}
```

### **Launch Scatter Buffalo**
```http
POST /api/buffalo/launch-game
Authorization: Bearer {token}
Content-Type: application/json

{
  "type_id": 1,
  "provider_id": 23,
  "game_id": 42,        // Scatter buffalo
  "room_id": 2
}
```

### **Generate Game URL with Game Type**
```http
POST /api/buffalo/game-url
Authorization: Bearer {token}
Content-Type: application/json

{
  "room_id": 2,
  "game_id": 42,        // Optional: 23 (normal) or 42 (scatter)
  "lobby_url": "https://yoursite.com"
}
```

---

## ✅ Testing Checklist

1. **Test Normal Buffalo (gameId: 23)**
   ```bash
   POST /api/buffalo/game-url
   Body: {"room_id": 1, "game_id": 23}
   ```
   - Should return game URL with `gameId=23`

2. **Test Scatter Buffalo (gameId: 42)**
   ```bash
   POST /api/buffalo/game-url
   Body: {"room_id": 1, "game_id": 42}
   ```
   - Should return game URL with `gameId=42`

3. **Verify API Request Format**
   - Check logs to ensure `roomId` is sent as string
   - Verify `domain` is included in request
   - Confirm correct API endpoint is called

4. **Test Proxy with New Domain**
   - Load game URL with `prime.next-api.net`
   - Verify proxy works correctly
   - Check resource loading

---

## 📝 Important Notes

1. **Domain Parameter:** 
   - **REQUIRED** by provider API
   - Default value: `prime.com` (update in `.env` if different)
   - Get correct domain from provider

2. **Room ID Format:**
   - Must be sent as **string** (`"1"`, `"2"`, etc.)
   - Not integer (`1`, `2`, etc.)
   - Provider API requirement

3. **Game ID Values:**
   - `23` = Normal Buffalo (default)
   - `42` = Scatter Buffalo
   - Can be specified in requests

4. **Game Server URL:**
   - New format: `https://prime.next-api.net`
   - Old format still supported for backward compatibility
   - Proxy validation updated for both

5. **API Response:**
   - Returns direct game URL with all parameters
   - No need to manually construct URLs
   - URL format: `https://prime.next-api.net/?gameId=X&roomId=Y&uid=...&token=...&lobbyUrl=...`

---

## 🔍 Response Example

**Provider API Response:**
```json
{
  "url": "https://prime.next-api.net/?gameId=42&roomId=1&uid=9789d102-de7c-4d07-beb6-34f05bee7e83&token=dc98b4ebf5e3c4d7aced87614a0a34505117b0a9696b22ebb67aebc5c6282f04&lobbyUrl=https%3A%2F%2Fbuffaloking788.com"
}
```

**Your API Response:**
```json
{
  "code": 1,
  "msg": "Success",
  "data": {
    "game_url": "https://prime.next-api.net/?gameId=42&roomId=1&uid=...&token=...&lobbyUrl=...",
    "room_info": {
      "min_bet": 50,
      "name": "50 အခန်း",
      "level": "Low"
    }
  }
}
```

---

## 🚨 Migration Steps

1. **Update `.env` file:**
   ```env
   BUFFALO_API_URL=https://api-ms3.african-buffalo.club/api/game-login
   BUFFALO_DOMAIN=prime.com
   ```

2. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```

3. **Test API calls:**
   - Test normal buffalo (gameId: 23)
   - Test scatter buffalo (gameId: 42)
   - Verify roomId is sent as string

4. **Update frontend (if needed):**
   - Add `game_id` parameter support
   - Update game launch to support both types

---

## 📚 Related Documentation

- Provider API Docs: [Google Docs](https://docs.google.com/document/d/1SU0UsWhlbUzSyvv5NpNaPtT7r2yd8-i3J8sOhZ5pyUc/edit?tab=t.0)
- Example Game URL: `https://prime.next-api.net/?gameId=42&roomId=1&uid=...&token=...&lobbyUrl=...`

---

## ✅ Summary

- ✅ API endpoint updated to correct URL
- ✅ `roomId` sent as string (not integer)
- ✅ `domain` parameter required
- ✅ Support for both game types (23 and 42)
- ✅ Proxy validation updated for new game server
- ✅ Backward compatibility maintained

