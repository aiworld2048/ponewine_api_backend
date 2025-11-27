# Buffalo Game - .env Configuration Guide

## 📋 Required .env Variables

Based on your `config/buffalo.php`, here's what you need in your `.env` file:

### **Required Variables** (Must be set)

```env
# Buffalo Game Provider Configuration
BUFFALO_API_URL=https://api-ms3.african-buffalo.club/api/game-login
BUFFALO_DOMAIN=prime.com
```

### **Optional Variables** (Have defaults, but you can override)

```env
# Game Server URL (Provider's lobby URL)
BUFFALO_GAME_SERVER_URL=https://prime.next-api.net

# Site Configuration
BUFFALO_SITE_NAME=https://maxwinmyanmar.pro
BUFFALO_SITE_PREFIX=mxm
BUFFALO_SITE_URL=https://maxwinmyanmar.pro

# Game Settings
BUFFALO_GAME_ID=23
BUFFALO_API_TIMEOUT=30
```

---

## 🔄 Changes from Old Setup

### **Remove These (No Longer Used):**
```env
# ❌ REMOVE - These are no longer used
BUFFALO_ENVIRONMENT=staging
BUFFALO_API_STAGING_URL=https://staging-ms.african-buffalo.net/api/game-login
BUFFALO_API_PRODUCTION_URL=https://ms.african-buffalo.net/api/game-login
```

### **Use This Instead:**
```env
# ✅ USE THIS - Single API URL
BUFFALO_API_URL=https://api-ms3.african-buffalo.club/api/game-login
```

---

## 📝 Complete .env Template

Add this to your `.env` file:

```env
# ============================================
# Buffalo Game Provider Configuration
# ============================================

# Game Login API URL (REQUIRED)
BUFFALO_API_URL=https://api-ms3.african-buffalo.club/api/game-login

# Domain name provided by provider (REQUIRED)
BUFFALO_DOMAIN=prime.com

# Game Server URL (Provider's lobby URL)
BUFFALO_GAME_SERVER_URL=https://prime.next-api.net

# Site Configuration
BUFFALO_SITE_NAME=https://maxwinmyanmar.pro
BUFFALO_SITE_PREFIX=mxm
BUFFALO_SITE_URL=https://maxwinmyanmar.pro

# Game Settings
BUFFALO_GAME_ID=23
BUFFALO_API_TIMEOUT=30
```

---

## ✅ Verification Steps

1. **Update your `.env` file** with the variables above

2. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```

3. **Verify configuration:**
   ```bash
   php artisan tinker
   ```
   Then run:
   ```php
   config('buffalo.api.url')
   config('buffalo.domain')
   config('buffalo.game_server_url')
   ```

4. **Test the API:**
   - Try launching a game
   - Check logs for API calls
   - Verify the correct API URL is being used

---

## 🚨 Important Notes

1. **BUFFALO_DOMAIN:** 
   - **REQUIRED** - Get the correct value from your provider
   - Current default: `prime.com`
   - Update if your provider gave you a different domain

2. **BUFFALO_API_URL:**
   - **REQUIRED** - This is the Game Login API endpoint
   - Current: `https://api-ms3.african-buffalo.club/api/game-login`
   - If provider gives you a different URL, update this

3. **BUFFALO_GAME_SERVER_URL:**
   - This is where games load from
   - Current: `https://prime.next-api.net`
   - Used as `lobbyUrl` in API requests

4. **BUFFALO_SITE_URL:**
   - Your website URL (where players are redirected on exit)
   - Update if different from default

---

## 🔍 What Each Variable Does

| Variable | Purpose | Required | Default |
|----------|---------|----------|---------|
| `BUFFALO_API_URL` | Game Login API endpoint | ✅ Yes | `https://api-ms3.african-buffalo.club/api/game-login` |
| `BUFFALO_DOMAIN` | Domain provided by provider | ✅ Yes | `prime.com` |
| `BUFFALO_GAME_SERVER_URL` | Game server URL (lobby URL) | ❌ No | `https://prime.next-api.net` |
| `BUFFALO_SITE_NAME` | Your site name | ❌ No | `https://maxwinmyanmar.pro` |
| `BUFFALO_SITE_PREFIX` | UID prefix | ❌ No | `mxm` |
| `BUFFALO_SITE_URL` | Your website URL | ❌ No | `https://maxwinmyanmar.pro` |
| `BUFFALO_GAME_ID` | Default game ID (23=normal, 42=scatter) | ❌ No | `23` |
| `BUFFALO_API_TIMEOUT` | API request timeout (seconds) | ❌ No | `30` |

---

## 🎯 Quick Setup Checklist

- [ ] Remove old `BUFFALO_ENVIRONMENT`, `BUFFALO_API_STAGING_URL`, `BUFFALO_API_PRODUCTION_URL`
- [ ] Add `BUFFALO_API_URL=https://api-ms3.african-buffalo.club/api/game-login`
- [ ] Add `BUFFALO_DOMAIN=prime.com` (or your provider's domain)
- [ ] Add `BUFFALO_GAME_SERVER_URL=https://prime.next-api.net`
- [ ] Update `BUFFALO_SITE_URL` if your website URL is different
- [ ] Run `php artisan config:clear`
- [ ] Test game launch

---

## 💡 Example .env Section

```env
# Buffalo Game Provider
BUFFALO_API_URL=https://api-ms3.african-buffalo.club/api/game-login
BUFFALO_DOMAIN=prime.com
BUFFALO_GAME_SERVER_URL=https://prime.next-api.net
BUFFALO_SITE_URL=https://yoursite.com
BUFFALO_GAME_ID=23
```

That's it! The config file has sensible defaults, so you only need to set the required ones.

