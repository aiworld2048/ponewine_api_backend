# Postman Testing Guide: BalanceUpdateCallbackController

## Endpoint Information

**HTTP Method:** `POST`

**Endpoint URL:** 
```
http://localhost/api/shan/client/balance-update
```
*(Replace `localhost` with your actual domain/IP)*

For local XAMPP development:
```
http://localhost/ponewine_api_backend/public/api/shan/client/balance-update
```

---

## Headers

Set the following headers in Postman:

| Key | Value |
|-----|-------|
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |

---

## Request Body Structure

The endpoint expects a JSON payload with the following structure:

### Required Fields:
- `wager_code` (string, max:255) - Unique wager identifier
- `players` (array) - Array of player objects
  - `players.*.player_id` (string, max:255) - Must match an existing `user_name` in the `users` table
  - `players.*.balance` (numeric, min:0) - New balance for the player
- `timestamp` (string) - Timestamp string

### Optional Fields:
- `game_type_id` (integer) - Game type identifier
- `banker_balance` (numeric) - Banker's balance
- `total_player_net` (numeric) - Total player net amount
- `banker_amount_change` (numeric) - Banker amount change

---

## Example JSON Payload

### Scenario 1: Player Win (Balance Increase)

```json
{
  "wager_code": "WAGER-2025-001-ABC123",
  "game_type_id": 15,
  "players": [
    {
      "player_id": "test_player_001",
      "balance": 1050.50
    }
  ],
  "banker_balance": 9500.00,
  "timestamp": "2025-01-28T10:30:00Z",
  "total_player_net": 50.50,
  "banker_amount_change": -50.50
}
```

### Scenario 2: Player Loss (Balance Decrease)

```json
{
  "wager_code": "WAGER-2025-002-XYZ789",
  "game_type_id": 15,
  "players": [
    {
      "player_id": "test_player_001",
      "balance": 950.00
    }
  ],
  "banker_balance": 10500.00,
  "timestamp": "2025-01-28T10:35:00Z",
  "total_player_net": -50.00,
  "banker_amount_change": 50.00
}
```

### Scenario 3: Multiple Players

```json
{
  "wager_code": "WAGER-2025-003-MULTI",
  "game_type_id": 15,
  "players": [
    {
      "player_id": "test_player_001",
      "balance": 1020.00
    },
    {
      "player_id": "test_player_002",
      "balance": 980.50
    }
  ],
  "banker_balance": 9000.00,
  "timestamp": "2025-01-28T10:40:00Z",
  "total_player_net": 0.50,
  "banker_amount_change": -0.50
}
```

### Scenario 4: No Balance Change

```json
{
  "wager_code": "WAGER-2025-004-NOCHANGE",
  "game_type_id": 15,
  "players": [
    {
      "player_id": "test_player_001",
      "balance": 1000.00
    }
  ],
  "banker_balance": 10000.00,
  "timestamp": "2025-01-28T10:45:00Z",
  "total_player_net": 0.00,
  "banker_amount_change": 0.00
}
```

---

## Expected Responses

### Success Response (200 OK)

```json
{
  "status": "success",
  "code": "SUCCESS",
  "message": "Balances updated successfully."
}
```

### Already Processed (200 OK) - Duplicate wager_code

```json
{
  "status": "success",
  "code": "ALREADY_PROCESSED",
  "message": "Wager already processed."
}
```

### Validation Error (400 Bad Request)

```json
{
  "status": "error",
  "code": "INVALID_REQUEST_DATA",
  "message": "Invalid request data: ..."
}
```

### Internal Server Error (500)

```json
{
  "status": "error",
  "code": "INTERNAL_SERVER_ERROR",
  "message": "Internal server error: ..."
}
```

### Player Not Found (500) - When player_id doesn't exist

The endpoint will return a 500 error if a `player_id` doesn't match any `user_name` in the database.

---

## Testing Steps in Postman

1. **Open Postman** and create a new request

2. **Set Method:** Select `POST` from the dropdown

3. **Enter URL:** 
   ```
   http://localhost/ponewine_api_backend/public/api/shan/client/balance-update
   ```

4. **Set Headers:**
   - Go to the "Headers" tab
   - Add `Content-Type: application/json`
   - Add `Accept: application/json`

5. **Set Body:**
   - Go to the "Body" tab
   - Select `raw`
   - Choose `JSON` from the dropdown
   - Paste one of the example payloads above

6. **Important:** Before testing:
   - Ensure the `player_id` in your payload exists as a `user_name` in your `users` table
   - The user must have a wallet (created via bavix/wallet package)
   - Each `wager_code` must be unique (or you'll get "ALREADY_PROCESSED")

7. **Send Request:** Click "Send" button

8. **Check Logs:** 
   - Check Laravel logs: `storage/logs/laravel.log`
   - Look for entries prefixed with "ClientSite:"

---

## Prerequisites

Before testing, ensure:

1. ✅ Database is running
2. ✅ Laravel application is running
3. ✅ User exists in `users` table with `user_name` matching your `player_id`
4. ✅ User has a wallet (bavix/wallet package)
5. ✅ `shan_key.secret_key` is configured in your config file
6. ✅ `processed_wager_callbacks` table exists (from migration)

---

## Common Issues

### Issue: "Player not found"
**Solution:** Make sure the `player_id` in your payload matches an existing `user_name` in the `users` table.

### Issue: "Duplicate wager_code"
**Solution:** Each `wager_code` must be unique. To test again with the same wager_code, either:
- Use a different `wager_code`
- Delete the record from `processed_wager_callbacks` table

### Issue: "Provider secret key not configured"
**Solution:** Make sure `config/shan_key.php` exists and has `secret_key` defined.

### Issue: SQL errors
**Solution:** Ensure all migrations are run and database tables exist.

---

## Quick Test Checklist

- [ ] Postman request created with POST method
- [ ] URL is correct (with `/api/shan/client/balance-update`)
- [ ] Headers set (`Content-Type: application/json`)
- [ ] JSON body is valid
- [ ] `wager_code` is unique (not used before)
- [ ] `player_id` exists in database as `user_name`
- [ ] User has a wallet
- [ ] Application logs are accessible

---

## Notes

- The endpoint **automatically calculates** the balance difference between the player's current balance and the new balance from the provider
- If `balanceDifference > 0`, it deposits to the wallet
- If `balanceDifference < 0`, it withdraws from the wallet using `forceWithdrawFloat`
- The endpoint uses database transactions for data integrity
- All operations are logged with "ClientSite:" prefix

