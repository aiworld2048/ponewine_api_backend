# Buffalo Game API Integration - Existing Project Flow

## 📋 Overview

The Buffalo game integration follows a **webhook-based architecture** where the game provider calls your API endpoints to:
- Get user balance
- Update balance (bet/win transactions)
- Launch games for authenticated users

---

## 🏗️ Architecture Components

### 1. **Service Layer** (`BuffaloGameService.php`)
Handles core business logic:
- **UID Generation**: Creates 32-character unique IDs from usernames
- **Token Generation**: Creates 64-character authentication tokens
- **Token Verification**: Validates tokens for API requests
- **Game URL Generation**: Builds game launch URLs with authentication
- **Room Management**: Handles different betting rooms (50, 500, 5000, 10000)

### 2. **Controller Layer** (`BuffaloGameController.php`)
Handles HTTP requests and responses:
- Public webhook endpoints (called by game provider)
- Protected frontend endpoints (called by your frontend)
- Proxy endpoints (for HTTPS game loading)

### 3. **Model Layer** (`LogBuffaloBet.php`)
Stores transaction history and bet logs

### 4. **Wallet Service** (`WalletService.php`)
Handles all wallet transactions using `bavix/laravel-wallet` package

---

## 🔌 API Endpoints

### **Public Webhook Endpoints** (No Authentication Required)

These are called by the Buffalo game provider:

#### 1. **Get User Balance**
```
POST /api/buffalo/get-user-balance
```

**Request:**
```json
{
  "uid": "mxmbase64encodedusername...",
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

**Flow:**
1. Validates `uid` and `token`
2. Extracts username from UID
3. Finds user in database
4. Returns current wallet balance as integer

---

#### 2. **Change Balance (Bet/Win)**
```
POST /api/buffalo/change-balance
```

**Request:**
```json
{
  "uid": "mxmbase64encodedusername...",
  "token": "sha256hash...",
  "changemoney": 50,      // Positive = win, Negative = loss
  "bet": 100,             // Bet amount
  "win": 150,             // Win amount
  "gameId": 23            // Buffalo game ID
}
```

**Response:**
```json
{
  "code": 1,
  "msg": "Balance updated successfully"
}
```

**Flow:**
1. Validates token and extracts user
2. Determines if `changemoney > 0` (win) or `< 0` (loss)
3. Uses `WalletService` to deposit or withdraw
4. Logs transaction to `log_buffalo_bets` table
5. Returns success/failure response

**Transaction Logic:**
- `changemoney > 0` → `WalletService::deposit()` with `TransactionName::GameWin`
- `changemoney < 0` → `WalletService::withdraw()` with `TransactionName::GameLoss`

---

### **Protected Frontend Endpoints** (Requires `auth:sanctum`)

These are called by your frontend application:

#### 3. **Generate Game Auth**
```
GET /api/buffalo/game-auth
Headers: Authorization: Bearer {token}
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
    "available_rooms": {
      "1": {"min_bet": 50, "name": "50 အခန်း", "level": "Low"},
      "2": {"min_bet": 500, "name": "500 အခန်း", "level": "Medium"}
    },
    "all_rooms": {...},
    "user_balance": 1000.50
  }
}
```

**Purpose:** Get authentication data and available rooms for the logged-in user

---

#### 4. **Generate Game URL**
```
POST /api/buffalo/game-url
Headers: Authorization: Bearer {token}
```

**Request:**
```json
{
  "room_id": 2,
  "lobby_url": "https://yoursite.com"  // Optional
}
```

**Response:**
```json
{
  "code": 1,
  "msg": "Success",
  "data": {
    "game_url": "http://prime7.wlkfkskakdf.com/?gameId=23&roomId=2&lobbyUrl=...&uid=...&token=...",
    "room_info": {"min_bet": 500, "name": "500 အခန်း", "level": "Medium"}
  }
}
```

**Purpose:** Generate a game launch URL with authentication for a specific room

---

#### 5. **Launch Game** (Unified Endpoint)
```
POST /api/buffalo/launch-game
Headers: Authorization: Bearer {token}
```

**Request:**
```json
{
  "type_id": 1,
  "provider_id": 23,      // 23 = Buffalo provider
  "game_id": 23,
  "room_id": 2            // Optional, defaults to 1
}
```

**Response:**
```json
{
  "code": 1,
  "msg": "Game launched successfully",
  "Url": "http://prime7.wlkfkskakdf.com/?gameId=23&roomId=2&...",
  "game_url": "http://prime7.wlkfkskakdf.com/?gameId=23&roomId=2&...",
  "room_info": {...},
  "user_balance": 1000.50
}
```

**Purpose:** Unified game launch endpoint compatible with existing frontend hooks

---

### **Proxy Endpoints** (No Authentication)

These handle HTTPS game loading from HTTP game server:

#### 6. **Proxy Game**
```
GET /api/buffalo/proxy-game?url={game_url}
```

**Purpose:** Proxies game HTML and rewrites all URLs to go through proxy

#### 7. **Proxy Resource**
```
GET /api/buffalo/proxy-resource?url={resource_url}
```

**Purpose:** Proxies game assets (CSS, JS, images) from HTTP game server

---

## 🔐 Authentication Flow

### **UID Generation**
```php
// Format: prefix(3) + base64_encoded_username + hash_padding = 32 chars
// Example: "mxm" + base64("player001") + hash = "mxmbase64encoded..."
```

**Process:**
1. Take username (e.g., "player001")
2. Base64 encode it (URL-safe)
3. Add site prefix ("mxm")
4. Pad to 32 characters with MD5 hash

### **Token Generation**
```php
// Format: SHA256 hash of (username + site_url + 'buffalo-persistent-token')
// Example: hash('sha256', 'player001' + 'https://maxwinmyanmar.pro' + 'buffalo-persistent-token')
```

**Process:**
1. Concatenate username + site URL + constant string
2. Generate SHA256 hash (64 characters)
3. This token is persistent (same for same user)

### **Token Verification**
```php
BuffaloGameService::verifyToken($uid, $token)
```

**Process:**
1. Extract username from UID
2. Find user in database
3. Generate expected token for that user
4. Compare with provided token using `hash_equals()`

---

## 💰 Transaction Flow

### **Bet Flow (Player Loses)**
```
1. Player places bet in game
2. Game calls: POST /api/buffalo/change-balance
   {
     "changemoney": -100,  // Negative = loss
     "bet": 100,
     "win": 0,
     "gameId": 23
   }
3. Controller validates token
4. WalletService::withdraw(user, 100, TransactionName::GameLoss)
5. LogBuffaloBet::create() - stores transaction
6. Returns success response
```

### **Win Flow (Player Wins)**
```
1. Player wins in game
2. Game calls: POST /api/buffalo/change-balance
   {
     "changemoney": 150,   // Positive = win
     "bet": 100,
     "win": 150,
     "gameId": 23
   }
3. Controller validates token
4. WalletService::deposit(user, 150, TransactionName::GameWin)
5. LogBuffaloBet::create() - stores transaction
6. Returns success response
```

---

## 🗄️ Database Structure

### **log_buffalo_bets Table**
Stores all bet transactions:

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `member_account` | string | Username |
| `player_id` | bigint | User ID |
| `player_agent_id` | bigint | Agent ID |
| `buffalo_game_id` | string | Game ID |
| `request_time` | timestamp | Transaction time |
| `bet_amount` | decimal(20,4) | Bet amount |
| `win_amount` | decimal(20,4) | Win amount |
| `payload` | json | Full request payload |
| `game_name` | string | "Buffalo Game" |
| `status` | string | "completed" / "pending" |
| `before_balance` | decimal(20,4) | Balance before transaction |
| `balance` | decimal(20,4) | Balance after transaction |

---

## 🎮 Room System

### **Room Configuration**
```php
1 => ['min_bet' => 50,    'name' => '50 အခန်း',   'level' => 'Low']
2 => ['min_bet' => 500,   'name' => '500 အခန်း',  'level' => 'Medium']
3 => ['min_bet' => 5000,  'name' => '5000 အခန်း', 'level' => 'High']
4 => ['min_bet' => 10000, 'name' => '10000 အခန်း', 'level' => 'VIP']
```

### **Room Availability Logic**
- User can only access rooms where `user_balance >= room_min_bet`
- `BuffaloGameService::getAvailableRooms($user)` filters rooms based on balance

---

## 🔄 Complete User Journey

### **1. User Login**
- User authenticates via `/api/login`
- Receives Sanctum token

### **2. User Requests Game**
- Frontend calls: `GET /api/buffalo/game-auth`
- Receives UID, token, and available rooms

### **3. User Selects Room**
- Frontend calls: `POST /api/buffalo/game-url` with `room_id`
- Receives game URL with authentication

### **4. Game Launches**
- Frontend loads game URL in iframe
- Game uses proxy endpoints for HTTPS loading

### **5. During Gameplay**
- Game periodically calls: `POST /api/buffalo/get-user-balance`
- Game calls: `POST /api/buffalo/change-balance` for each bet/win

### **6. Transaction Logging**
- All transactions logged to `log_buffalo_bets`
- Wallet transactions tracked via `bavix/laravel-wallet`

---

## 🔧 Configuration

### **Site Configuration** (in `BuffaloGameService.php`)
```php
private const SITE_NAME = 'https://maxwinmyanmar.pro';
private const SITE_PREFIX = 'mxm';
private const SITE_URL = 'https://maxwinmyanmar.pro';
```

### **Game Server URL**
```php
$baseUrl = 'http://prime7.wlkfkskakdf.com/';
$gameId = 23; // Buffalo game ID
```

---

## 📝 Key Design Patterns

1. **Service Layer Pattern**: Business logic separated into `BuffaloGameService`
2. **Repository Pattern**: Models handle database operations
3. **Dependency Injection**: `WalletService` injected into controller
4. **Transaction Safety**: All balance changes wrapped in `DB::beginTransaction()`
5. **Idempotency**: Token-based authentication prevents replay attacks
6. **Logging**: Comprehensive logging at every step

---

## 🚨 Error Handling

### **Common Error Responses**
```json
{
  "code": 0,
  "msg": "Invalid token" | "User not found" | "Transaction failed"
}
```

### **Error Scenarios**
1. **Invalid Token**: Token verification fails → Returns `code: 0`
2. **User Not Found**: UID doesn't match any user → Returns `code: 0`
3. **Insufficient Balance**: Withdrawal fails → Transaction rolled back
4. **Database Error**: Exception caught → Transaction rolled back, error logged

---

## 📊 Logging

All operations are logged with prefixes:
- `GameStar77 Buffalo` - General operations
- `6TriBet Buffalo` - Transaction operations
- `TriBet Buffalo` - Service layer operations

Log locations: `storage/logs/laravel.log`

---

## 🔐 Security Features

1. **Token Verification**: Every webhook request verified
2. **UID Validation**: UID format validated and username extracted
3. **Database Transactions**: Atomic operations for balance changes
4. **Proxy URL Validation**: Only allows game server URLs
5. **HTTPS Proxy**: Converts HTTP game server to HTTPS via proxy

---

## 🎯 Integration Checklist

When integrating a new game provider, you'll need:

- [ ] Service class (like `BuffaloGameService`)
- [ ] Controller class (like `BuffaloGameController`)
- [ ] Routes definition
- [ ] Model for transaction logging
- [ ] Migration for transaction table
- [ ] UID/Token generation logic
- [ ] Balance webhook endpoints
- [ ] Game launch endpoint
- [ ] Frontend integration endpoints
- [ ] Error handling and logging
- [ ] Database transaction safety

---

## 📚 Related Files

- `app/Services/BuffaloGameService.php` - Core service logic
- `app/Http/Controllers/Api/V1/Game/BuffaloGameController.php` - API endpoints
- `app/Models/LogBuffaloBet.php` - Transaction model
- `app/Services/WalletService.php` - Wallet operations
- `routes/api.php` - Route definitions
- `database/migrations/2025_10_14_031735_create_log_buffalo_bets_table.php` - Database schema

---

## 🔄 Next Steps for New Integration

1. **Analyze Provider API**: Understand their webhook format
2. **Create Service Class**: Implement authentication and URL generation
3. **Create Controller**: Implement webhook and frontend endpoints
4. **Create Model & Migration**: For transaction logging
5. **Add Routes**: Define API endpoints
6. **Test Integration**: Use Postman to test webhooks
7. **Frontend Integration**: Connect frontend to launch endpoints

