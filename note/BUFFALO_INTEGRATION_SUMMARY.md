# Buffalo Game Integration - Quick Reference Summary

## 🎯 Core Flow Overview

```
┌─────────────┐
│   Frontend  │
│  (User App) │
└──────┬──────┘
       │
       │ 1. GET /api/buffalo/game-auth (with Bearer token)
       ▼
┌─────────────────────────┐
│  BuffaloGameController  │
│  generateGameAuth()     │
└──────┬──────────────────┘
       │
       │ 2. Generate UID & Token
       ▼
┌─────────────────────────┐
│  BuffaloGameService     │
│  - generateUid()        │
│  - generateToken()      │
└──────┬──────────────────┘
       │
       │ 3. Return auth data + available rooms
       ▼
┌─────────────┐
│   Frontend  │
└──────┬──────┘
       │
       │ 4. POST /api/buffalo/game-url (room_id)
       ▼
┌─────────────────────────┐
│  BuffaloGameController  │
│  generateGameUrl()      │
└──────┬──────────────────┘
       │
       │ 5. Build game URL with auth
       ▼
┌─────────────┐
│   Frontend  │
│  Loads Game │
└──────┬──────┘
       │
       │ 6. Game calls webhooks
       ▼
┌─────────────────────────────────────────┐
│  Buffalo Game Provider (Webhooks)       │
│  - POST /api/buffalo/get-user-balance    │
│  - POST /api/buffalo/change-balance      │
└──────┬───────────────────────────────────┘
       │
       │ 7. Token verification + balance update
       ▼
┌─────────────────────────┐
│  WalletService          │
│  - deposit() / withdraw()│
└──────┬──────────────────┘
       │
       │ 8. Log transaction
       ▼
┌─────────────────────────┐
│  LogBuffaloBet Model    │
│  (Database)             │
└─────────────────────────┘
```

---

## 📁 File Structure

```
app/
├── Services/
│   ├── BuffaloGameService.php      # Core business logic
│   └── WalletService.php           # Wallet operations
├── Http/Controllers/Api/V1/Game/
│   └── BuffaloGameController.php   # API endpoints
├── Models/
│   └── LogBuffaloBet.php           # Transaction logging
└── Enums/
    └── TransactionName.php          # Transaction types

routes/
└── api.php                          # Route definitions

database/migrations/
└── 2025_10_14_031735_create_log_buffalo_bets_table.php
```

---

## 🔑 Key Components

### **1. BuffaloGameService**
**Purpose:** Core business logic for Buffalo game integration

**Key Methods:**
- `generateUid($userName)` → Returns 32-char UID
- `generateToken($userName)` → Returns 64-char token
- `verifyToken($uid, $token)` → Validates authentication
- `extractUserNameFromUid($uid)` → Gets username from UID
- `getGameUrl($user, $roomId, $lobbyUrl)` → Builds game URL
- `getAvailableRooms($user)` → Filters rooms by balance

### **2. BuffaloGameController**
**Purpose:** Handles all HTTP requests

**Public Endpoints (Webhooks):**
- `getUserBalance()` → Returns user balance
- `changeBalance()` → Updates balance (bet/win)

**Protected Endpoints (Frontend):**
- `generateGameAuth()` → Returns auth data
- `generateGameUrl()` → Returns game launch URL
- `launchGame()` → Unified game launch

**Proxy Endpoints:**
- `proxyGame()` → Proxies game HTML
- `proxyResource()` → Proxies game assets

### **3. WalletService**
**Purpose:** Handles wallet transactions

**Key Methods:**
- `deposit($user, $amount, $transactionName, $meta)`
- `withdraw($user, $amount, $transactionName, $meta)`

**Uses:** `bavix/laravel-wallet` package

### **4. LogBuffaloBet Model**
**Purpose:** Stores transaction history

**Fields:**
- `member_account`, `player_id`, `bet_amount`, `win_amount`
- `before_balance`, `balance`, `payload` (JSON)

---

## 🔄 Request/Response Examples

### **Webhook: Get Balance**
```http
POST /api/buffalo/get-user-balance
Content-Type: application/json

{
  "uid": "mxmbase64encoded...",
  "token": "sha256hash..."
}
```

**Response:**
```json
{
  "code": 1,
  "msg": "Success",
  "balance": 1000
}
```

### **Webhook: Change Balance**
```http
POST /api/buffalo/change-balance
Content-Type: application/json

{
  "uid": "mxmbase64encoded...",
  "token": "sha256hash...",
  "changemoney": -50,
  "bet": 100,
  "win": 50,
  "gameId": 23
}
```

**Response:**
```json
{
  "code": 1,
  "msg": "Balance updated successfully"
}
```

### **Frontend: Get Auth**
```http
GET /api/buffalo/game-auth
Authorization: Bearer {sanctum_token}
```

**Response:**
```json
{
  "code": 1,
  "msg": "Success",
  "data": {
    "auth": {
      "uid": "mxm...",
      "token": "sha256...",
      "user_name": "player001"
    },
    "available_rooms": {...},
    "user_balance": 1000.50
  }
}
```

---

## 🔐 Authentication Details

### **UID Format**
```
Prefix (3 chars) + Base64(username) + Hash padding = 32 chars total
Example: "mxm" + base64("player001") + md5(...) = "mxmbase64encodedhash..."
```

### **Token Format**
```
SHA256(username + site_url + 'buffalo-persistent-token')
Example: hash('sha256', 'player001' + 'https://maxwinmyanmar.pro' + 'buffalo-persistent-token')
```

### **Token Verification Flow**
1. Extract username from UID
2. Find user in database
3. Generate expected token
4. Compare using `hash_equals()` (timing-safe)

---

## 💰 Transaction Logic

### **Win Transaction** (`changemoney > 0`)
```php
WalletService::deposit($user, $changeAmount, TransactionName::GameWin, [
    'buffalo_game_id' => $gameId,
    'bet_amount' => $betAmount,
    'win_amount' => $winAmount,
    'provider' => 'buffalo',
    'transaction_type' => 'game_win'
]);
```

### **Loss Transaction** (`changemoney < 0`)
```php
WalletService::withdraw($user, abs($changeAmount), TransactionName::GameLoss, [
    'buffalo_game_id' => $gameId,
    'bet_amount' => $betAmount,
    'win_amount' => $winAmount,
    'provider' => 'buffalo',
    'transaction_type' => 'game_loss'
]);
```

---

## 🎮 Room System

**Rooms:**
- Room 1: Min bet 50
- Room 2: Min bet 500
- Room 3: Min bet 5000
- Room 4: Min bet 10000

**Logic:** User can only access rooms where `balance >= min_bet`

---

## 🛡️ Security Features

1. **Token Verification**: Every webhook request verified
2. **Database Transactions**: Atomic balance updates
3. **UID Validation**: Format validated before processing
4. **Proxy URL Validation**: Only game server URLs allowed
5. **HTTPS Proxy**: Converts HTTP game to HTTPS

---

## 📊 Database Transaction Flow

```php
DB::beginTransaction();
try {
    // 1. Verify token
    // 2. Update wallet (deposit/withdraw)
    // 3. Log to LogBuffaloBet
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Log error
}
```

---

## 🚨 Error Handling

**Common Errors:**
- `code: 0, msg: "Invalid token"` → Token verification failed
- `code: 0, msg: "User not found"` → UID doesn't match user
- `code: 0, msg: "Transaction failed"` → Wallet operation failed

**All errors logged to:** `storage/logs/laravel.log`

---

## 📝 Integration Checklist for New Game

When adding a new game provider:

- [ ] Create Service class (e.g., `NewGameService.php`)
- [ ] Create Controller class (e.g., `NewGameController.php`)
- [ ] Create Model for transaction logging
- [ ] Create Migration for transaction table
- [ ] Implement UID/Token generation
- [ ] Implement webhook endpoints:
  - [ ] Get balance endpoint
  - [ ] Change balance endpoint
- [ ] Implement frontend endpoints:
  - [ ] Generate auth endpoint
  - [ ] Launch game endpoint
- [ ] Add routes to `routes/api.php`
- [ ] Test with Postman
- [ ] Add error handling and logging
- [ ] Test database transactions
- [ ] Document API endpoints

---

## 🔍 Key Differences from Other Integrations

### **Buffalo vs Shan Integration**

| Feature | Buffalo | Shan |
|---------|---------|------|
| **Auth Method** | UID + Token | Signature-based |
| **Balance Update** | Real-time webhooks | Batch callback |
| **Transaction Type** | Individual bets | Wager settlements |
| **Game Launch** | Direct URL | Provider-specific |
| **Logging** | `LogBuffaloBet` | `ReportTransaction` |

---

## 📚 Related Documentation

- `BUFFALO_GAME_INTEGRATION_FLOW.md` - Detailed flow documentation
- `POSTMAN_TESTING_GUIDE.md` - Postman testing guide (for Shan, but similar pattern)

---

## 🎯 Quick Start for Testing

1. **Get Auth:**
   ```bash
   curl -X GET http://localhost/api/buffalo/game-auth \
     -H "Authorization: Bearer {token}"
   ```

2. **Test Balance Webhook:**
   ```bash
   curl -X POST http://localhost/api/buffalo/get-user-balance \
     -H "Content-Type: application/json" \
     -d '{"uid":"mxm...","token":"sha256..."}'
   ```

3. **Test Change Balance:**
   ```bash
   curl -X POST http://localhost/api/buffalo/change-balance \
     -H "Content-Type: application/json" \
     -d '{"uid":"mxm...","token":"sha256...","changemoney":-50,"bet":100,"win":50,"gameId":23}'
   ```

---

## 💡 Best Practices

1. **Always use database transactions** for balance updates
2. **Log all operations** for debugging
3. **Validate tokens** on every webhook request
4. **Use timing-safe comparison** (`hash_equals`) for tokens
5. **Refresh user model** after balance changes
6. **Handle exceptions** gracefully with rollback
7. **Return consistent response format** (`code`, `msg`)

