# Azure Token Refresh Implementation

## Overview
This document explains the automatic token refresh mechanism implemented for Azure SSO integration when syncing users from Microsoft Graph API.

## Problem
Previously, when users tried to sync from Azure, they would get the error:
```
No Azure access token found. Please login with Microsoft SSO first.
```

Even though they were logged in via Azure SSO. This happened because:
1. Access tokens expire after a certain period (typically 1 hour)
2. The session might lose token data due to session rotation or timeout
3. No automatic refresh mechanism was in place when Graph API returned 401 errors

## Solution Implemented

### 1. Enhanced `getValidAzureToken()` Method (AuthController.php)

**Location:** `app/Http/Controllers/AuthController.php`

**Improvements:**
- Better detection of missing tokens vs expired tokens
- Checks if user is logged in via Azure SSO (has `user_id` from Azure)
- Handles cases where refresh token is missing but access token is still valid
- More detailed logging for debugging
- Clear error messages indicating whether re-authentication is needed

**Logic Flow:**
```
1. Check if access token exists in session
   ├─ NO → Check if user logged in via Azure SSO
   │       ├─ YES → Return "session expired, re-authenticate" message
   │       └─ NO → Return "login first" message
   └─ YES → Continue to step 2

2. Check if refresh token exists
   ├─ NO → Check if access token is still valid
   │       ├─ YES → Use existing token
   │       └─ NO → Return "re-authenticate" message
   └─ YES → Continue to step 3

3. Check token expiry
   ├─ EXPIRED (or expiring in <5 min) → Attempt refresh
   │   ├─ SUCCESS → Return new token
   │   └─ FAILED → Return error message
   └─ VALID → Return existing token
```

### 2. New `fetchUsersFromGraphWithRefresh()` Method (UserController.php)

**Location:** `app/Http/Controllers/UserController.php`

**Purpose:** Automatically handle 401 errors during Graph API calls by refreshing the token and retrying.

**Features:**
- Detects 401 Unauthorized responses from Graph API
- Automatically triggers token refresh
- Retries the API call with new token (max 1 retry to prevent loops)
- Logs all refresh attempts for debugging

**Example:**
```php
// Old approach - would fail on 401
$graphUsers = $this->fetchUsersFromGraph($accessToken);

// New approach - auto-refreshes on 401
$graphUsers = $this->fetchUsersFromGraphWithRefresh($accessToken, $request);
```

### 3. Improved Error Messages in `syncFromAzure()`

**Location:** `app/Http/Controllers/UserController.php`

**Enhancements:**
- Detects when re-authentication is required
- Returns `require_reauth: true` flag for frontend handling
- Provides clear instructions to users

**Response Examples:**

**Success:**
```json
{
    "success": true,
    "message": "Successfully synced 15 users from Azure AD.",
    "data": {
        "total_synced": 15,
        "created": 5,
        "updated": 10,
        "errors": []
    }
}
```

**Token Expired (needs re-auth):**
```json
{
    "success": false,
    "message": "Your Azure session has expired. Please log out and log in again with Microsoft SSO to sync users.",
    "require_reauth": true
}
```

**Token Refresh Failed:**
```json
{
    "success": false,
    "message": "Failed to refresh token. Please login again."
}
```

## Token Lifecycle

### Initial Login (Azure SSO)
1. User clicks "Login with Microsoft"
2. Azure returns: `access_token`, `refresh_token`, `expires_in`
3. Application stores in session:
   - `azure_access_token`
   - `azure_refresh_token`
   - `azure_token_expires_at`

### During Sync Operation
1. Check if access token exists
2. Check if token is expired (or expires in <5 minutes)
3. If expired:
   - Use refresh token to get new access token
   - Update session with new tokens
4. If refresh fails:
   - Return error with re-authentication message

### During Graph API Call
1. Make API request with current access token
2. If 401 received:
   - Refresh token
   - Retry request with new token
3. If retry fails:
   - Return error to user

## Configuration Requirements

Ensure your Azure AD app has these settings:

**Required Scopes:**
- `openid`
- `profile`
- `email`
- `User.Read`
- `User.Read.All` (requires admin consent)
- `offline_access` (required for refresh tokens)

**Environment Variables (.env):**
```env
AZURE_TENANT_ID=your-tenant-id
AZURE_CLIENT_ID=your-client-id
AZURE_CLIENT_SECRET=your-client-secret
AZURE_REDIRECT_URI=http://localhost/auth/callback
```

## Testing

### Test Token Refresh
1. Login via Azure SSO
2. Wait for token to expire (or manually modify `azure_token_expires_at` in session)
3. Click "Sync Azure"
4. Token should automatically refresh and sync should succeed

### Test 401 Handling
1. Login via Azure SSO
2. Manually invalidate the access token (if possible)
3. Click "Sync Azure"
4. System should detect 401, refresh token, and retry

### Test Re-authentication Flow
1. Login via Azure SSO
2. Clear session data for tokens (simulate session loss)
3. Click "Sync Azure"
4. Should receive message to re-authenticate

## Troubleshooting

### Common Issues

**1. "No Azure access token found"**
- **Cause:** User never logged in via SSO or session was cleared
- **Solution:** Log out and log in again with Microsoft SSO

**2. "Failed to refresh token"**
- **Cause:** Refresh token expired or invalid
- **Solution:** Re-authenticate with Azure SSO

**3. "Azure session expired"**
- **Cause:** Access token expired and refresh token missing
- **Solution:** Log out and log in again

### Debug Logging

Check Laravel logs for detailed information:
```bash
tail -f storage/logs/laravel.log | grep -i azure
```

**Key log messages:**
- `Azure access token expired or expiring soon, attempting to refresh...`
- `Azure access token refreshed successfully`
- `Azure Graph API returned 401, attempting token refresh...`
- `Token refresh failed during 401 handling`

## Security Considerations

1. **Refresh Token Rotation:** Microsoft may issue new refresh tokens. The code handles this by updating the session with any new refresh token received.

2. **Token Storage:** Tokens are stored in session (server-side), not in cookies or localStorage.

3. **Token Expiry Buffer:** Tokens are refreshed 5 minutes before actual expiry to prevent race conditions.

4. **Retry Limit:** Maximum 1 retry to prevent infinite loops.

## Files Modified

1. `app/Http/Controllers/AuthController.php`
   - Enhanced `getValidAzureToken()` method
   - Improved error handling and logging

2. `app/Http/Controllers/UserController.php`
   - Added `fetchUsersFromGraphWithRefresh()` method
   - Updated `syncFromAzure()` with better error messages
   - Kept original `fetchUsersFromGraph()` for backward compatibility

## Next Steps

1. Test the implementation in development environment
2. Monitor logs for token refresh events
3. Update frontend to handle `require_reauth` flag (optional)
4. Consider adding a "Re-authenticate" button in the UI

## Support

For issues or questions, check:
- Laravel logs: `storage/logs/laravel.log`
- Azure AD App registrations in Azure Portal
- Microsoft Graph API documentation
