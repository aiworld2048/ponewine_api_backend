# Buffalo Game API Updates - Based on Official API Documentation

## 📋 Changes Made

### 1. **Database Migration - Added `bet_uid` and `room_id`**
   - **File:** `database/migrations/2025_11_25_044546_add_bet_uid_to_log_buffalo_bets_table.php`
   - **Changes:**
     - Added `bet_uid` column (string, unique) for idempotency
     - Added `room_id` column (integer, nullable) to track room
     - Added unique index on `bet_uid` to prevent duplicates

### 2. **Model Update - LogBuffaloBet**
   - **File:** `app/Models/LogBuffaloBet.php`
   - **Changes:**
     - Added `bet_uid` to `$fillable` array
     - Added `room_id` to `$fillable` array

### 3. **Controller Update - changeBalance Method**
   - **File:** `app/Http/Controllers/Api/V1/Game/BuffaloGameController.php`
   - **Key Changes:**

#### **a) Updated Validation Rules**
   ```php
   // Added required bet_uid parameter
   'bet_uid' => 'required|string',
   
   // Support both gameId/gameld and roomId/roomld (API docs have typos)
   'gameId' => 'nullable|integer',
   'gameld' => 'nullable|integer',
   'roomId' => 'nullable|integer',
   'roomld' => 'nullable|integer',
   ```

#### **b) Added Idempotency Check**
   - Checks if `bet_uid` already exists in database
   - If duplicate found, returns existing result without processing
   - Prevents duplicate transactions from being processed

#### **c) Updated Response Format**
   - **Before:** `msg: "Balance updated successfully"`
   - **After:** `msg: "{balance_in_cents}"` (as per API docs)
   - Response message now contains user balance in cents as string

#### **d) Updated Logging**
   - `logBuffaloBet()` method now accepts and stores:
     - `bet_uid` - Unique bet identifier
     - `gameId` - Game ID (handles both gameId and gameld)
     - `roomId` - Room ID (handles both roomId and roomld)

---

## 🔄 API Endpoint Changes

### **POST /api/buffalo/change-balance**

#### **Request Parameters (Updated)**
```http
POST /api/buffalo/change-balance
Content-Type: application/x-www-form-urlencoded  (or application/json)

uid: string (required, max 50 chars)
bet_uid: string (required) - NEW: Unique bet identifier
token: string (required)
changemoney: integer (required) - win - bet
bet: integer (required)
win: integer (required)
gameId: integer (optional) - or gameld
gameld: integer (optional) - API docs typo, but supported
roomId: integer (optional) - or roomld
roomld: integer (optional) - API docs typo, but supported
```

#### **Response Format (Updated)**
```json
{
  "code": 1,
  "msg": "100000"  // User balance in cents (as string, per API docs)
}
```

**On Error:**
```json
{
  "code": 0,
  "msg": "Error message"
}
```

#### **Idempotency Behavior**
- If `bet_uid` already exists → Returns success with current balance
- No duplicate transaction processing
- Prevents double-charging or double-crediting

---

## 📊 Database Schema Changes

### **log_buffalo_bets Table**
```sql
ALTER TABLE log_buffalo_bets 
ADD COLUMN bet_uid VARCHAR(255) NULL AFTER member_account,
ADD COLUMN room_id INT NULL AFTER buffalo_game_id,
ADD UNIQUE INDEX bet_uid (bet_uid),
ADD INDEX bet_uid_index (bet_uid);
```

---

## ✅ Testing Checklist

### **Test Scenarios:**

1. **Normal Transaction (Win)**
   ```bash
   POST /api/buffalo/change-balance
   {
     "uid": "mxm...",
     "bet_uid": "unique-bet-123",
     "token": "sha256...",
     "changemoney": 50,
     "bet": 100,
     "win": 150,
     "gameId": 23,
     "roomId": 2
   }
   ```
   **Expected:** `code: 1, msg: "{balance_in_cents}"`

2. **Normal Transaction (Loss)**
   ```bash
   POST /api/buffalo/change-balance
   {
     "uid": "mxm...",
     "bet_uid": "unique-bet-124",
     "token": "sha256...",
     "changemoney": -50,
     "bet": 100,
     "win": 50,
     "gameId": 23,
     "roomId": 2
   }
   ```
   **Expected:** `code: 1, msg: "{balance_in_cents}"`

3. **Duplicate bet_uid (Idempotency)**
   ```bash
   POST /api/buffalo/change-balance
   {
     "uid": "mxm...",
     "bet_uid": "unique-bet-123",  # Same as previous
     "token": "sha256...",
     "changemoney": 50,
     "bet": 100,
     "win": 150,
     "gameId": 23
   }
   ```
   **Expected:** `code: 1, msg: "{current_balance_in_cents}"` (no new transaction)

4. **Missing bet_uid**
   ```bash
   POST /api/buffalo/change-balance
   {
     "uid": "mxm...",
     "token": "sha256...",
     "changemoney": 50,
     "bet": 100,
     "win": 150
   }
   ```
   **Expected:** Validation error

5. **Parameter Name Variations**
   ```bash
   POST /api/buffalo/change-balance
   {
     "uid": "mxm...",
     "bet_uid": "unique-bet-125",
     "token": "sha256...",
     "changemoney": 50,
     "bet": 100,
     "win": 150,
     "gameld": 23,  # Using typo version
     "roomld": 2    # Using typo version
   }
   ```
   **Expected:** Should work correctly (handles both variants)

---

## 🔧 Migration Instructions

1. **Run the migration:**
   ```bash
   php artisan migrate
   ```

2. **Verify the changes:**
   ```sql
   DESCRIBE log_buffalo_bets;
   -- Should show bet_uid and room_id columns
   ```

3. **Test the endpoints:**
   - Use Postman to test with form data
   - Verify idempotency works
   - Check response format matches API docs

---

## 📝 Notes

1. **Form Data Support:** Laravel automatically handles both `application/x-www-form-urlencoded` and `application/json` request formats.

2. **Parameter Name Handling:** The code supports both correct names (`gameId`, `roomId`) and typo versions (`gameld`, `roomld`) from the API docs.

3. **Balance Format:** Balance is returned in cents (multiplied by 100) as an integer string in the `msg` field, per API documentation.

4. **Idempotency:** The `bet_uid` ensures that if the game provider sends the same transaction twice (due to network issues, retries, etc.), it won't be processed twice.

5. **Backward Compatibility:** The `getUserBalance` endpoint remains unchanged and continues to work as before.

---

## 🚨 Important

- **Always test idempotency** to ensure duplicate requests don't cause double transactions
- **Monitor logs** for any issues with `bet_uid` uniqueness
- **Verify balance calculations** are correct (cents vs dollars)
- **Check database** after migration to ensure columns were added correctly

