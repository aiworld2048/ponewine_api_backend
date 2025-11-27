# Postman Testing Guide: Buffalo Game Launch

## 📋 Overview

This guide shows you how to test the Buffalo game `launchGame` endpoint using Postman. The endpoint requires authentication via Laravel Sanctum.

---

## 🔐 Step 1: Get Authentication Token

Before testing `launchGame`, you need to authenticate and get a token.

### **Login Endpoint**

**Method:** `POST`  
**URL:** `http://localhost/ponewine_api_backend/public/api/login`  
*(Adjust domain/port as needed)*

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
  "user_name": "player001",
  "password": "your_password"
}
```

**Expected Response:**
```json
{
  "status": "success",
  "message": "User login successfully.",
  "data": {
    "user": {
      "id": 123,
      "name": "Player Name",
      "user_name": "player001",
      "phone": "09123456789",
      "email": "player@example.com",
      "balance": 1000.50,
      "status": 1
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890..."
  }
}
```

**Important:** Copy the `token` value from the response. You'll need it for the next step.

---

## 🎮 Step 2: Launch Buffalo Game

### **Launch Game Endpoint**

**Method:** `POST`  
**URL:** `http://localhost/ponewine_api_backend/public/api/buffalo/launch-game`

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your_token_here}
```

**Replace `{your_token_here}` with the token from Step 1.**

---

## 📝 Request Body Examples

### **Example 1: Normal Buffalo (Default)**

```json
{
  "type_id": 1,
  "provider_id": 23,
  "game_id": 23,
  "room_id": 1
}
```

**Parameters:**
- `type_id`: `1` (required, integer)
- `provider_id`: `23` (required, must be 23 for Buffalo)
- `game_id`: `23` (required, 23 = normal buffalo, 42 = scatter buffalo)
- `room_id`: `1` (optional, 1-4, defaults to 1)

---

### **Example 2: Scatter Buffalo**

```json
{
  "type_id": 1,
  "provider_id": 23,
  "game_id": 42,
  "room_id": 2
}
```

**Parameters:**
- `game_id`: `42` (scatter buffalo)
- `room_id`: `2` (room with min bet 500)

---

### **Example 3: High Stakes Room**

```json
{
  "type_id": 1,
  "provider_id": 23,
  "game_id": 23,
  "room_id": 3
}
```

**Parameters:**
- `room_id`: `3` (room with min bet 5000)

---

## ✅ Expected Responses

### **Success Response (200 OK)**

```json
{
  "code": 1,
  "msg": "Game launched successfully",
  "Url": "https://prime.next-api.net/?gameId=23&roomId=1&uid=...&token=...&lobbyUrl=...",
  "game_url": "https://prime.next-api.net/?gameId=23&roomId=1&uid=...&token=...&lobbyUrl=...",
  "room_info": {
    "min_bet": 50,
    "name": "50 အခန်း",
    "level": "Low"
  },
  "user_balance": 1000.50
}
```

---

### **Error Responses**

#### **1. Not Authenticated (401)**
```json
{
  "code": 0,
  "msg": "User not authenticated"
}
```

**Solution:** Make sure you include the `Authorization: Bearer {token}` header.

---

#### **2. Room Not Available (200)**
```json
{
  "code": 0,
  "msg": "Room not available for your balance level"
}
```

**Solution:** User's balance is less than the room's minimum bet requirement.

---

#### **3. Game Provider Not Supported (200)**
```json
{
  "code": 0,
  "msg": "Game provider not supported"
}
```

**Solution:** `provider_id` must be `23` for Buffalo games.

---

#### **4. API Error (200)**
```json
{
  "code": 0,
  "msg": "Failed to launch game: Game Login API failed: HTTP 500 - ..."
}
```

**Solution:** Provider's Game Login API returned an error. Check logs for details.

---

## 🧪 Complete Testing Workflow

### **Step-by-Step in Postman:**

1. **Create Login Request:**
   - Method: `POST`
   - URL: `http://localhost/ponewine_api_backend/public/api/login`
   - Headers: `Content-Type: application/json`
   - Body: 
     ```json
     {
       "user_name": "player001",
       "password": "password123"
     }
     ```
   - Send request
   - Copy the `token` from response

2. **Create Launch Game Request:**
   - Method: `POST`
   - URL: `http://localhost/ponewine_api_backend/public/api/buffalo/launch-game`
   - Headers:
     - `Content-Type: application/json`
     - `Authorization: Bearer {paste_token_here}`
   - Body:
     ```json
     {
       "type_id": 1,
       "provider_id": 23,
       "game_id": 23,
       "room_id": 1
     }
     ```
   - Send request
   - Check response for `game_url`

---

## 📊 Room Configuration

| Room ID | Min Bet | Name | Level |
|---------|---------|------|-------|
| 1 | 50 | 50 အခန်း | Low |
| 2 | 500 | 500 အခန်း | Medium |
| 3 | 5000 | 5000 အခန်း | High |
| 4 | 10000 | 10000 အခန်း | VIP |

**Note:** User can only access rooms where `balance >= min_bet`

---

## 🎯 Game Types

| Game ID | Game Type | Description |
|---------|-----------|-------------|
| 23 | Normal Buffalo | Standard Buffalo game |
| 42 | Scatter Buffalo | Scatter version of Buffalo |

---

## 🔧 Postman Collection Setup

### **Environment Variables (Optional)**

Create a Postman environment with:

```
base_url: http://localhost/ponewine_api_backend/public
auth_token: (will be set after login)
```

Then use:
- URL: `{{base_url}}/api/buffalo/launch-game`
- Authorization: `Bearer {{auth_token}}`

### **Pre-request Script (Auto Login)**

You can add a pre-request script to automatically get a token:

```javascript
// Auto-login before launch game request
pm.sendRequest({
    url: pm.environment.get("base_url") + '/api/login',
    method: 'POST',
    header: {
        'Content-Type': 'application/json'
    },
    body: {
        mode: 'raw',
        raw: JSON.stringify({
            user_name: 'player001',
            password: 'password123'
        })
    }
}, function (err, res) {
    if (!err) {
        var jsonData = res.json();
        if (jsonData.data && jsonData.data.token) {
            pm.environment.set("auth_token", jsonData.data.token);
        }
    }
});
```

---

## 📋 Quick Test Checklist

- [ ] User exists in database
- [ ] User has sufficient balance for selected room
- [ ] Login request successful
- [ ] Token copied correctly
- [ ] Authorization header set: `Bearer {token}`
- [ ] `provider_id` = 23 (Buffalo)
- [ ] `game_id` = 23 (normal) or 42 (scatter)
- [ ] `room_id` = 1, 2, 3, or 4
- [ ] Response contains `game_url`

---

## 🚨 Common Issues

### **Issue: "User not authenticated"**
- **Check:** Authorization header is set correctly
- **Check:** Token is not expired
- **Solution:** Re-login to get a new token

### **Issue: "Room not available"**
- **Check:** User's balance in database
- **Check:** Room's minimum bet requirement
- **Solution:** Ensure user has enough balance or choose a lower room

### **Issue: "Failed to launch game: IP address not whitelisted" (HTTP 403)**
- **Error:** `Game Login API rejected request: IP address not whitelisted`
- **Cause:** Your server's IP is not whitelisted by the provider
- **Solution:**
  1. Check your server IP in logs: `storage/logs/laravel.log`
  2. Look for `"server_ip": "xxx.xxx.xxx.xxx"` in error logs
  3. Or run: `php check_server_ip.php`
  4. Contact provider with your IP address
  5. Request whitelisting for: `https://api-ms3.african-buffalo.club/api/game-login`
  6. Wait for provider confirmation
  7. Test again
- **See:** `BUFFALO_IP_WHITELIST_GUIDE.md` for detailed steps

### **Issue: "Failed to launch game" (Other Errors)**
- **Check:** `.env` file has correct `BUFFALO_API_URL`
- **Check:** `.env` file has correct `BUFFALO_DOMAIN`
- **Check:** Provider's API is accessible
- **Solution:** Check Laravel logs for detailed error

### **Issue: Invalid token format**
- **Check:** Token includes "Bearer " prefix
- **Check:** No extra spaces in header
- **Solution:** Format: `Authorization: Bearer 1|abc123...`

---

## 📝 Example cURL Commands

### **Login:**
```bash
curl -X POST http://localhost/ponewine_api_backend/public/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_name": "player001",
    "password": "password123"
  }'
```

### **Launch Game:**
```bash
curl -X POST http://localhost/ponewine_api_backend/public/api/buffalo/launch-game \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "type_id": 1,
    "provider_id": 23,
    "game_id": 23,
    "room_id": 1
  }'
```

---

## 🔍 Debugging Tips

1. **Check Logs:**
   - Location: `storage/logs/laravel.log`
   - Look for: `6TriBet Buffalo Game Launch` entries
   - Check: Generated UID and token values

2. **Verify Token:**
   - Use `excepted_uid_token.php` script to generate expected values
   - Compare with logged values

3. **Test Room Availability:**
   - Check user balance: `GET /api/buffalo/game-auth` (with auth)
   - See available rooms in response

4. **Test Provider API:**
   - Check if provider's Game Login API is accessible
   - Verify `.env` configuration

---

## 📚 Related Endpoints

### **Get Game Auth (Alternative)**
```
GET /api/buffalo/game-auth
Authorization: Bearer {token}
```

**Response:**
```json
{
  "code": 1,
  "msg": "Success",
  "data": {
    "auth": {
      "uid": "...",
      "token": "...",
      "user_name": "player001"
    },
    "available_rooms": {...},
    "user_balance": 1000.50
  }
}
```

### **Generate Game URL (Alternative)**
```
POST /api/buffalo/game-url
Authorization: Bearer {token}
Body: {
  "room_id": 1,
  "game_id": 23
}
```

---

## ✅ Success Indicators

When the request is successful, you should see:

1. **Response Code:** `200 OK`
2. **Response Body:** Contains `code: 1` and `game_url`
3. **Game URL Format:** `https://prime.next-api.net/?gameId=X&roomId=Y&uid=...&token=...&lobbyUrl=...`
4. **Logs:** Success entry in `storage/logs/laravel.log`

---

## 🎯 Testing Scenarios

### **Scenario 1: Normal Buffalo, Room 1**
```json
{
  "type_id": 1,
  "provider_id": 23,
  "game_id": 23,
  "room_id": 1
}
```
**Expected:** Game URL with `gameId=23&roomId=1`

### **Scenario 2: Scatter Buffalo, Room 2**
```json
{
  "type_id": 1,
  "provider_id": 23,
  "game_id": 42,
  "room_id": 2
}
```
**Expected:** Game URL with `gameId=42&roomId=2`

### **Scenario 3: No Room ID (Default)**
```json
{
  "type_id": 1,
  "provider_id": 23,
  "game_id": 23
}
```
**Expected:** Defaults to `room_id: 1`

---

## 💡 Pro Tips

1. **Save Token as Variable:** Use Postman's "Set Variable" feature to save token automatically
2. **Use Collection Variables:** Store base URL and common values
3. **Test Different Rooms:** Try all 4 rooms to verify balance checks
4. **Test Both Game Types:** Test both normal (23) and scatter (42)
5. **Check Logs:** Always check Laravel logs for detailed debugging info

---

## 📞 Need Help?

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify user exists and has balance
3. Check `.env` configuration
4. Test provider API connectivity
5. Use `excepted_uid_token.php` to verify UID/token generation

