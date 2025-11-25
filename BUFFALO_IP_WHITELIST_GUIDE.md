# Buffalo Game - IP Whitelist Issue Resolution

## 🚨 Error: "Unauthorized. Your IP is not allowed."

### **Problem**

The provider's Game Login API is returning **HTTP 403** with the message:
```
"Unauthorized. Your IP is not allowed."
```

This means your server's IP address is **not whitelisted** by the provider.

---

## 🔍 How to Find Your Server IP

### **Method 1: Check Server IP in Logs**

The error logs now include your server IP. Look for:
```
"server_ip": "xxx.xxx.xxx.xxx"
```

### **Method 2: Check via PHP**

Create a temporary file `check_ip.php`:

```php
<?php
echo "Server IP: " . ($_SERVER['SERVER_ADDR'] ?? 'unknown') . "\n";
echo "Request IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
echo "HTTP X-Forwarded-For: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'none') . "\n";
```

Access it via browser or:
```bash
php check_ip.php
```

### **Method 3: Check via Command Line**

```bash
# On Linux/Unix
curl ifconfig.me
# or
curl ipinfo.io/ip

# On Windows (PowerShell)
Invoke-RestMethod -Uri "https://api.ipify.org"
```

### **Method 4: Check Laravel Logs**

After a failed request, check `storage/logs/laravel.log`:
```
"server_ip": "xxx.xxx.xxx.xxx"
```

---

## ✅ Solution Steps

### **Step 1: Identify Your Server's Public IP**

Your server's **public IP address** is what the provider sees. This could be:
- Your server's direct IP (if no proxy/CDN)
- Your proxy/CDN IP (if using Cloudflare, etc.)
- Your hosting provider's IP

### **Step 2: Contact Provider**

Contact the Buffalo game provider and provide:
1. **Your server's public IP address**
2. **Request:** "Please whitelist this IP for Game Login API access"
3. **API Endpoint:** `https://api-ms3.african-buffalo.club/api/game-login`

### **Step 3: Verify Whitelist**

After provider confirms IP is whitelisted:
1. Test the `launchGame` endpoint again
2. Check logs for successful API calls
3. Verify game URL is returned

---

## 🔧 Temporary Workarounds

### **Option 1: Use Proxy/VPN**

If you have access to a whitelisted IP:
- Use a proxy server with whitelisted IP
- Configure Laravel HTTP client to use proxy

**Example:**
```php
$response = Http::timeout($timeout)
    ->withOptions([
        'verify' => false,
        'proxy' => 'http://proxy-server:port', // If needed
    ])
    ->asJson()
    ->post($apiUrl, $payload);
```

### **Option 2: Test from Different Server**

If you have another server with whitelisted IP:
- Temporarily test from that server
- Or use it as a proxy

---

## 📝 Updated Error Handling

The code now provides more helpful error messages:

### **HTTP 403 (IP Not Allowed)**
```
Game Login API rejected request: IP address not whitelisted. 
Your server IP (xxx.xxx.xxx.xxx) needs to be whitelisted by the provider. 
Contact provider to add your IP to their whitelist.
```

### **HTTP 401 (Authentication Failed)**
```
Game Login API authentication failed. Check your domain and credentials.
```

### **Other Errors**
```
Game Login API failed: HTTP {status} - {error details}
```

---

## 🔍 Debugging Information

The logs now include:
- **Server IP:** Your server's IP address
- **Request IP:** Client's IP (if different)
- **API URL:** The endpoint being called
- **Payload:** Request data (token partially hidden)

**Check logs at:** `storage/logs/laravel.log`

Look for entries:
- `Buffalo Game Login API - Failed`
- `6TriBet Buffalo Game Launch Error`

---

## 📞 Information to Provide Provider

When contacting the provider, provide:

1. **Your Server IP:** `xxx.xxx.xxx.xxx`
2. **API Endpoint:** `https://api-ms3.african-buffalo.club/api/game-login`
3. **Domain:** Your domain (from `BUFFALO_DOMAIN` config)
4. **Error Message:** "HTTP 403 - Unauthorized. Your IP is not allowed."
5. **Request:** "Please whitelist this IP address for Game Login API access"

---

## ✅ Verification Checklist

After IP is whitelisted:

- [ ] Test `launchGame` endpoint
- [ ] Check response code is `200 OK`
- [ ] Verify `game_url` is returned
- [ ] Check logs show successful API call
- [ ] Test with different game types (23 and 42)
- [ ] Test with different rooms (1-4)

---

## 🚨 Important Notes

1. **IP Whitelisting is Required:**
   - Provider's security measure
   - Cannot be bypassed
   - Must contact provider

2. **IP May Change:**
   - If using dynamic IP, notify provider
   - Consider using static IP
   - Or use proxy with static IP

3. **Multiple Servers:**
   - If you have staging/production servers
   - Both IPs need to be whitelisted
   - Or use same IP (via load balancer)

4. **Development vs Production:**
   - Development server IP may differ
   - May need separate whitelist for dev
   - Or use production server for testing

---

## 🔧 Alternative: Check if Provider Has Different Endpoint

Some providers have:
- Different endpoints for different environments
- Staging endpoint with different IP requirements
- Test endpoint that doesn't require whitelisting

**Check with provider:**
- Is there a staging/test endpoint?
- Are there different IP requirements?
- Can we use a different authentication method?

---

## 📊 Expected Behavior After Whitelisting

**Before (403 Error):**
```json
{
  "code": 0,
  "msg": "Failed to launch game: Game Login API rejected request: IP address not whitelisted..."
}
```

**After (Success):**
```json
{
  "code": 1,
  "msg": "Game launched successfully",
  "Url": "https://prime.next-api.net/?gameId=23&roomId=1&uid=...&token=...&lobbyUrl=...",
  "game_url": "...",
  "room_info": {...},
  "user_balance": 1000.50
}
```

---

## 💡 Quick Fix Summary

1. **Find your server IP** (check logs or use `check_ip.php`)
2. **Contact provider** with your IP address
3. **Request whitelisting** for Game Login API
4. **Wait for confirmation** from provider
5. **Test again** - should work after whitelisting

The error is on the provider's side (IP restriction), not your code. Once your IP is whitelisted, the integration will work correctly.

