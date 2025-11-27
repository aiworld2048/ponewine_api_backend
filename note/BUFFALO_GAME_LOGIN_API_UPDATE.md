# Buffalo Game Login API Integration Update

## 📋 Overview

Updated the Buffalo game integration to use the **provider's Game Login API** instead of generating game URLs locally. This ensures we're using the official API endpoint as specified in the provider's documentation.

---

## 🔄 Changes Made

### 1. **Created Configuration File**
   - **File:** `config/buffalo.php`
   - **Purpose:** Centralized configuration for Buffalo game provider
   - **Settings:**
     - API URLs (staging and production)
     - Environment selection
     - Site configuration
     - Domain name (provided by provider)
     - Game ID
     - Request timeout

### 2. **Updated BuffaloGameService**
   - **New Method:** `getGameUrlFromProvider()`
     - Calls the provider's Game Login API
     - Handles authentication (UID and token)
     - Sends required parameters: `uid`, `token`, `gameId`, `roomId`, `lobbyUrl`, `domain`
     - Returns game URL from provider response
   
   - **Updated Method:** `generateGameUrl()`
     - Now calls `getGameUrlFromProvider()` instead of building URL locally
     - Maintains backward compatibility

### 3. **Updated Controller Methods**
   - **`generateGameUrl()` method:**
     - Added try-catch for API errors
     - Returns appropriate error messages
   
   - **`launchGame()` method:**
     - Removed manual UID/token appending (now handled by API)
     - Updated error handling
     - Improved logging

---

## 📡 API Integration Details

### **Provider Game Login API**

**Endpoint:**
- Staging: `https://staging-ms.african-buffalo.net/api/game-login`
- Production: `https://ms.african-buffalo.net/api/game-login` (configured via env)

**Request Format:** JSON

**Request Parameters:**
```json
{
  "uid": "string",           // Party A's unique ID (32 chars)
  "token": "string",         // Data verification mark (64 chars)
  "gameId": 23,              // Game ID (23 for buffalo)
  "roomId": 1,               // Room ID (1-4)
  "lobbyUrl": "string",      // Redirect URL when player exits
  "domain": "string"         // Domain name provided by provider (optional)
}
```

**Response:**
```json
{
  "url": "https://game-server.com/game?params..."
}
```

---

## ⚙️ Configuration

### **Environment Variables**

Add these to your `.env` file:

```env
# Buffalo Game Provider Configuration
BUFFALO_ENVIRONMENT=staging
BUFFALO_API_STAGING_URL=https://staging-ms.african-buffalo.net/api/game-login
BUFFALO_API_PRODUCTION_URL=https://ms.african-buffalo.net/api/game-login

# Site Configuration
BUFFALO_SITE_NAME=https://maxwinmyanmar.pro
BUFFALO_SITE_PREFIX=mxm
BUFFALO_SITE_URL=https://maxwinmyanmar.pro

# Provider Settings
BUFFALO_DOMAIN=                    # Domain name provided by provider
BUFFALO_GAME_ID=23
BUFFALO_API_TIMEOUT=30
```

### **Production Setup**

For production, update `.env`:
```env
BUFFALO_ENVIRONMENT=production
BUFFALO_DOMAIN=your-domain.com    # Get from provider
```

---

## 🔄 Flow Comparison

### **Before (Local URL Generation)**
```
1. User requests game
2. Generate UID and token locally
3. Build game URL manually: baseUrl + query params
4. Return URL to frontend
```

### **After (API-Based)**
```
1. User requests game
2. Generate UID and token locally
3. Call provider's Game Login API with auth data
4. Provider returns game URL
5. Return URL to frontend
```

---

## ✅ Benefits

1. **Official Integration:** Uses provider's official API endpoint
2. **Consistency:** Ensures game URLs match provider's format
3. **Flexibility:** Provider can update game URLs without code changes
4. **Security:** Provider handles authentication and URL generation
5. **Error Handling:** Better error messages from provider API

---

## 🧪 Testing

### **Test Game URL Generation**

```bash
# Test via Postman or curl
POST /api/buffalo/game-url
Headers: Authorization: Bearer {token}
Body: {
  "room_id": 2,
  "lobby_url": "https://yoursite.com"
}
```

**Expected Response:**
```json
{
  "code": 1,
  "msg": "Success",
  "data": {
    "game_url": "https://game-server.com/game?params...",
    "room_info": {...}
  }
}
```

### **Test Launch Game**

```bash
POST /api/buffalo/launch-game
Headers: Authorization: Bearer {token}
Body: {
  "type_id": 1,
  "provider_id": 23,
  "game_id": 23,
  "room_id": 2
}
```

**Expected Response:**
```json
{
  "code": 1,
  "msg": "Game launched successfully",
  "Url": "https://game-server.com/game?params...",
  "game_url": "https://game-server.com/game?params...",
  "room_info": {...},
  "user_balance": 1000.50
}
```

---

## 🚨 Error Handling

### **API Connection Errors**
- **Error:** Connection timeout or network failure
- **Response:** `code: 0, msg: "Failed to generate game URL: Connection error"`
- **Action:** Check network connectivity and API URL

### **API Response Errors**
- **Error:** Invalid response format or missing 'url' field
- **Response:** `code: 0, msg: "Failed to generate game URL: Invalid response"`
- **Action:** Check API response format and provider status

### **Authentication Errors**
- **Error:** Invalid UID or token
- **Response:** Provider API will return error
- **Action:** Verify UID/token generation logic

---

## 📝 Logging

All API calls are logged with:
- Request details (user, room, game ID)
- Response status
- Error messages (if any)
- Full trace for debugging

**Log Location:** `storage/logs/laravel.log`

**Log Prefixes:**
- `Buffalo Game Login API - Request`
- `Buffalo Game Login API - Success`
- `Buffalo Game Login API - Failed`
- `Buffalo Game Login API - Error`

---

## 🔧 Migration Steps

1. **Add configuration file:**
   ```bash
   # File already created: config/buffalo.php
   ```

2. **Update `.env` file:**
   ```env
   BUFFALO_ENVIRONMENT=staging
   BUFFALO_DOMAIN=                    # Get from provider
   # ... other settings
   ```

3. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```

4. **Test the integration:**
   - Test game URL generation
   - Test game launch
   - Verify error handling

5. **Monitor logs:**
   - Check for API errors
   - Verify successful API calls
   - Monitor response times

---

## 📚 Related Files

- `config/buffalo.php` - Configuration file
- `app/Services/BuffaloGameService.php` - Service with API integration
- `app/Http/Controllers/Api/V1/Game/BuffaloGameController.php` - Controller methods

---

## 🔍 Key Differences

| Aspect | Before | After |
|--------|--------|-------|
| **URL Generation** | Local (manual) | Provider API |
| **Authentication** | Manual (appended to URL) | Handled by API |
| **Error Handling** | Basic | Comprehensive |
| **Configuration** | Hardcoded | Config file + env |
| **Flexibility** | Low | High (provider controls URLs) |

---

## ⚠️ Important Notes

1. **Domain Parameter:** The `domain` parameter is required by the API but may be optional. Get the correct domain value from the provider.

2. **Environment:** Make sure to set `BUFFALO_ENVIRONMENT` correctly (staging vs production).

3. **Timeout:** API timeout is set to 30 seconds by default. Adjust if needed.

4. **SSL Verification:** Currently disabled (`verify => false`). Enable in production if provider uses valid SSL certificates.

5. **Backward Compatibility:** The `generateGameUrl()` method signature remains the same, so existing code continues to work.

---

## 🎯 Next Steps

1. **Get Domain from Provider:** Contact provider to get the correct `domain` value
2. **Test in Staging:** Test thoroughly in staging environment
3. **Update Production Config:** Set production URL and domain
4. **Monitor:** Watch logs for any API issues
5. **Optimize:** Adjust timeout and retry logic if needed

