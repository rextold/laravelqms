# CSRF Token Mismatch Fix

## Problem
When submitting the organization settings form via AJAX, a CSRF token mismatch error was occurring:
```
CSRF token mismatch.
```

## Root Cause
The AJAX fetch request was not including the CSRF token in the request headers. Laravel's VerifyCsrfToken middleware checks for the CSRF token in:
1. POST parameter `_token` (included in FormData via `@csrf`)
2. Header `X-CSRF-TOKEN` (was missing)
3. Header `X-XSRF-TOKEN` (was missing)
4. Cookie `XSRF-TOKEN` (may not be set)

When the first three options are missing or invalid, Laravel rejects the request.

## Solution
Updated the AJAX form submission to explicitly include the CSRF token in the request headers:

```javascript
// Get CSRF token from the form or meta tag
const csrfToken = document.querySelector('input[name="_token"]')?.value 
    || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const response = await fetch(
    `/${orgCode}/admin/organization-settings`,
    {
        method: 'PUT',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken  // ← Added this line
        },
        body: formData
    }
);
```

## How It Works

1. **Extract CSRF Token**: The code gets the CSRF token from either:
   - The hidden `_token` input field in the form (set by `@csrf`)
   - The meta tag `csrf-token` in the page head
   
2. **Include in Headers**: The token is sent as the `X-CSRF-TOKEN` header, which Laravel's middleware expects

3. **FormData Still Contains Token**: The FormData also contains the `_token` field from the form, providing a backup validation method

4. **Laravel Validates**: Laravel's VerifyCsrfToken middleware now finds the token in the headers and validates it successfully

## Files Modified
- `resources/views/admin/organization-settings.blade.php` - Added CSRF token header extraction and inclusion

## Verification Steps

1. Open the organization settings page in a browser
2. Open Developer Tools (F12)
3. Go to Network tab
4. Change a setting and click Save Settings
5. Check the PUT request to `/[org-code]/admin/organization-settings`
6. Verify the request headers include `X-CSRF-TOKEN`
7. Verify the response is 200 OK with `{"success": true}`

## Expected Headers

Your fetch request should now include:
```
X-CSRF-TOKEN: [token-value-here]
X-Requested-With: XMLHttpRequest
Accept: application/json
```

## Security
This approach is secure because:
- The CSRF token is generated server-side and unique per session
- The token is only valid for the current session
- The token expires after a set time (default: 2 hours)
- The token is bound to the user's session
- Using the X-CSRF-TOKEN header is the standard Laravel method for AJAX requests

## Next Steps
Try submitting the form again. You should now see:
- ✅ No CSRF token mismatch error
- ✅ Successful form submission (or validation errors if form data is invalid)
- ✅ Settings updated in real-time
- ✅ Success toast notification appears
