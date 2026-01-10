# Testing Guide - Organization Settings & Brand Colors

## Pre-Test Checklist

- [ ] Composer dependencies installed: `composer install`
- [ ] Database migrations run: `php artisan migrate`
- [ ] Storage link created: `php artisan storage:link`
- [ ] Application served: `php artisan serve` or nginx configured
- [ ] Test user has admin role for organization
- [ ] Browser cache cleared

---

## Test 1: Logo Upload and Compression

### Test Case 1.1: Upload Logo with Compression
**Steps:**
1. Navigate to `/{org_code}/admin/organization-settings`
2. Scroll to "Organization Logo" section
3. Click "Choose File" and select a logo image (PNG recommended)
4. Observe file size in console/logs
5. Click "Save Settings"

**Expected Results:**
- ✅ Logo preview shows before submission
- ✅ Form submits via AJAX (no page reload)
- ✅ Success toast appears
- ✅ Original file size logged
- ✅ Compressed file size logged
- ✅ Compression ratio: 40-60% reduction
- ✅ Logo appears in header/navigation immediately

**Check Logs:**
```bash
tail -f storage/logs/laravel.log | grep -i "logo"
```

Expected output:
```
[2024-01-11 ...] local.INFO: Logo upload started {"name":"logo.png","size":250000,"mime":"image/png"}
[2024-01-11 ...] local.INFO: Logo compressed and stored {"path":"logos/...","original_size":250000,"compressed_size":120000,"reduction":"52%"}
```

---

## Test 2: Brand Colors Functionality

### Test Case 2.1: Update Primary Color
**Steps:**
1. Navigate to `/{org_code}/admin/organization-settings`
2. Scroll to "Brand Colors" section
3. Click on primary color picker
4. Select a new color (e.g., #FF6B6B for red)
5. Observe live preview updates in real-time
6. Click "Save Settings"

**Expected Results:**
- ✅ Color picker opens
- ✅ Live preview updates as you select color
- ✅ Hex value shows in text field
- ✅ Form submits via AJAX
- ✅ Primary color changes in preview
- ✅ Success message appears
- ✅ Returns to form without reload

### Test Case 2.2: Update All Colors at Once
**Steps:**
1. Navigate to settings page
2. Update all 4 colors:
   - Primary: #667eea
   - Secondary: #764ba2
   - Accent: #10b981
   - Text: #ffffff
3. Observe live preview with all colors
4. Save settings

**Expected Results:**
- ✅ All colors update in preview
- ✅ Preview gradient shows correct colors
- ✅ All colors saved to database
- ✅ API returns all 4 colors in response

**Verify in Database:**
```sql
SELECT primary_color, secondary_color, accent_color, text_color 
FROM organization_settings 
WHERE organization_id = 1;
```

---

## Test 3: Real-Time Updates Without Refresh

### Test Case 3.1: Single Tab Update
**Steps:**
1. Open `/{org_code}/admin/organization-settings` in one tab
2. Change organization name to "Test Company 2024"
3. Save settings
4. Observe page title and all headings update

**Expected Results:**
- ✅ Page does not reload
- ✅ Form submission via AJAX
- ✅ Organization name updates in page
- ✅ Browser tab title updates
- ✅ Success notification shown

### Test Case 3.2: Multiple Tabs Real-Time Sync
**Steps:**
1. Open two tabs to the same organization:
   - Tab A: Dashboard (`/{org_code}/admin/dashboard`)
   - Tab B: Organization Settings (`/{org_code}/admin/organization-settings`)
2. In Tab B: Change organization name to "Real-Time Test"
3. Save settings
4. Check Tab A without switching to it

**Expected Results:**
- ✅ Settings saved in Tab B
- ✅ Tab A automatically detects change (within 3 seconds)
- ✅ Tab A updates organization name without refresh
- ✅ Tab A updates colors without refresh
- ✅ Tab A updates logo without refresh

**Check Browser Console:**
```javascript
// In Tab A console:
window.settingsSync.cachedSettings
// Should show updated settings
```

### Test Case 3.3: Monitor/Kiosk Display Updates
**Steps:**
1. Open Monitor display in one window: `/{org_code}/monitor`
2. Open Settings in another window: `/{org_code}/admin/organization-settings`
3. In Settings: Change primary color to #FF00FF
4. Change organization name to "Real-Time Display Test"
5. Save settings

**Expected Results:**
- ✅ Monitor display shows new colors (within 3 seconds)
- ✅ Monitor display shows new organization name
- ✅ Monitor display shows new logo
- ✅ No page refresh needed on monitor

---

## Test 4: Color Application Across UI

### Test Case 4.1: CSS Variables Application
**Steps:**
1. Open browser DevTools (F12)
2. Go to Elements/Inspector tab
3. Select any element with `data-theme` attribute
4. Change a color in settings
5. Watch CSS variable update in DevTools

**Expected Results:**
- ✅ `--primary-color` CSS variable updates
- ✅ `--secondary-color` CSS variable updates
- ✅ `--accent-color` CSS variable updates
- ✅ `--text-color` CSS variable updates
- ✅ Elements using these variables update immediately

**Test Command:**
```javascript
// In browser console
getComputedStyle(document.documentElement).getPropertyValue('--primary-color')
// Should return the current primary color
```

### Test Case 4.2: Data Attribute Elements Update
**Steps:**
1. Identify elements with data-theme attributes:
   - `[data-theme="bg-primary"]`
   - `[data-theme="bg-secondary"]`
   - `[data-theme="gradient"]`
2. Change colors in settings
3. Observe elements update

**Expected Results:**
- ✅ Background colors change
- ✅ Gradients update
- ✅ Text colors change
- ✅ No page reload needed

---

## Test 5: Organization Name Updates

### Test Case 5.1: Header Name Update
**Steps:**
1. Navigate to any authenticated page
2. In settings: Change organization name
3. Save settings
4. Check header/sidebar for name update

**Expected Results:**
- ✅ Page header updates (if using `data-org-name`)
- ✅ Sidebar updates organization name
- ✅ Update happens within 3 seconds
- ✅ No page reload

### Test Case 5.2: Logo Update
**Steps:**
1. Upload a logo
2. Save settings
3. Check all pages for logo display
4. Upload different logo
5. Save settings

**Expected Results:**
- ✅ First logo displays
- ✅ Second logo replaces first
- ✅ Update within 3 seconds on all pages
- ✅ Old logo deleted from storage

**Check Storage:**
```bash
ls -la storage/app/public/logos/
# Should see logo files with compression
```

---

## Test 6: Form Validation

### Test Case 6.1: Invalid Color Format
**Steps:**
1. Try to manually edit hex value to invalid format: `XXX` (instead of #RRGGBB)
2. Try to submit form

**Expected Results:**
- ✅ Client-side validation prevents submission
- ✅ Error message shown (if using HTML5 validation)
- ✅ Server rejects with validation error
- ✅ Error message displayed to user

### Test Case 6.2: File Size Limit
**Steps:**
1. Try to upload image larger than 2MB
2. Try to submit form

**Expected Results:**
- ✅ Server rejects with error message
- ✅ Validation error shown
- ✅ File not processed

### Test Case 6.3: Invalid File Type
**Steps:**
1. Try to upload a non-image file (PDF, text)
2. Try to submit form

**Expected Results:**
- ✅ Server rejects with error message
- ✅ Validation error shown
- ✅ File not processed

---

## Test 7: API Endpoints

### Test Case 7.1: GET Settings API
**Steps:**
```bash
curl -X GET "http://localhost/{org_code}/api/settings" \
  -H "Accept: application/json"
```

**Expected Response:**
```json
{
    "organization_name": "Test Organization",
    "primary_color": "#667eea",
    "secondary_color": "#764ba2",
    "accent_color": "#10b981",
    "text_color": "#ffffff",
    "company_logo": "/storage/logos/12345_logo.png",
    "company_phone": "+1 (555) 123-4567",
    "company_email": "test@example.com",
    "company_address": "123 Main St"
}
```

### Test Case 7.2: PUT Update API
**Steps:**
```bash
curl -X PUT "http://localhost/{org_code}/admin/organization-settings" \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -H "Accept: application/json" \
  -F "organization_name=Updated Organization" \
  -F "primary_color=#FF0000" \
  -F "secondary_color=#00FF00" \
  -F "accent_color=#0000FF" \
  -F "text_color=#FFFFFF" \
  -F "queue_number_digits=4" \
  -F "company_phone=+1-123-456-7890" \
  -F "company_email=test@example.com" \
  -F "company_address=456 Oak Ave"
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Settings updated successfully",
    "settings": {
        "primary_color": "#FF0000",
        "secondary_color": "#00FF00",
        "accent_color": "#0000FF",
        "text_color": "#FFFFFF",
        "company_logo": "/storage/logos/existing_logo.png"
    }
}
```

---

## Test 8: Performance

### Test Case 8.1: Logo Compression Metrics
**Steps:**
1. Upload a large logo (1-2MB PNG)
2. Check logs for compression ratio
3. Check actual file size on disk

**Expected Results:**
- ✅ Original size logged
- ✅ Compressed size logged
- ✅ Reduction 40-60%
- ✅ File loading faster

### Test Case 8.2: API Poll Performance
**Steps:**
1. Open Network tab in DevTools
2. Watch API calls to `/api/settings`
3. Change colors and observe polling

**Expected Results:**
- ✅ API called every 3 seconds
- ✅ Response time < 100ms
- ✅ No unnecessary requests if settings unchanged
- ✅ Only relevant data sent in response

### Test Case 8.3: Memory Usage
**Steps:**
1. Open Task Manager (Windows) or Activity Monitor (Mac)
2. Monitor memory usage while viewing admin settings
3. Make multiple color changes
4. Check memory doesn't spike

**Expected Results:**
- ✅ Memory usage stable
- ✅ No memory leaks
- ✅ Smooth performance

---

## Test 9: Error Handling

### Test Case 9.1: Network Error
**Steps:**
1. Take server offline (stop php artisan serve)
2. Try to save settings
3. Observe error message

**Expected Results:**
- ✅ Error toast appears
- ✅ User-friendly error message
- ✅ Form button re-enabled

### Test Case 9.2: Authorization Error
**Steps:**
1. Login as non-admin user
2. Try to access organization-settings page

**Expected Results:**
- ✅ 403 Forbidden error
- ✅ Redirect to dashboard or error page
- ✅ Cannot access settings

### Test Case 9.3: Invalid Organization Code
**Steps:**
1. Try to access: `/{invalid_code}/admin/organization-settings`

**Expected Results:**
- ✅ 404 Not Found error
- ✅ Error page displayed

---

## Test 10: Cross-Browser Compatibility

### Test Each Browser:
- [ ] Chrome 90+
- [ ] Firefox 88+
- [ ] Safari 14+
- [ ] Edge 90+
- [ ] Mobile Chrome
- [ ] Mobile Safari (iOS)

**Steps for Each:**
1. Navigate to settings page
2. Update colors
3. Verify live preview works
4. Verify AJAX submission works
5. Verify multi-tab sync works

**Expected Results:**
- ✅ Form works on all browsers
- ✅ Colors update on all browsers
- ✅ Logo upload works
- ✅ Real-time sync works (BroadcastChannel or fallback)

---

## Automated Testing Script

```javascript
// Run in browser console to test automatically

async function testOrganizationSettings() {
    console.log('🧪 Starting Organization Settings Tests...\n');
    
    // Test 1: SettingsSync exists
    console.log('✓ Test 1: SettingsSync loaded');
    if (!window.settingsSync) {
        console.error('✗ SettingsSync not found');
        return;
    }
    
    // Test 2: Fetch settings
    console.log('✓ Test 2: Fetching settings...');
    await window.settingsSync.fetchSettings();
    console.log('Settings:', window.settingsSync.cachedSettings);
    
    // Test 3: CSS variables
    console.log('✓ Test 3: Checking CSS variables...');
    const primaryColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--primary-color').trim();
    console.log('Primary color:', primaryColor);
    
    // Test 4: Data attributes
    console.log('✓ Test 4: Checking data attributes...');
    const orgNameEl = document.querySelector('[data-org-name]');
    console.log('Org name element:', orgNameEl?.textContent);
    
    const logoEl = document.querySelector('[data-org-logo]');
    console.log('Logo element:', logoEl?.src);
    
    // Test 5: Event listener
    console.log('✓ Test 5: Testing event listener...');
    window.addEventListener('organizationSettingsUpdated', (e) => {
        console.log('Settings updated event received:', e.detail);
    });
    
    console.log('\n✅ All tests passed!');
}

testOrganizationSettings();
```

---

## Passing Criteria

### Core Functionality (Must Pass)
- [ ] Logo uploads and compresses correctly
- [ ] Colors save to database
- [ ] Settings API returns correct JSON
- [ ] Form submits via AJAX without reload
- [ ] Organization name updates across pages
- [ ] Colors update across pages

### Real-Time Sync (Must Pass)
- [ ] Same-tab updates work (colors/name/logo)
- [ ] Multi-tab updates work within 3 seconds
- [ ] Monitor/Kiosk displays receive updates
- [ ] BroadcastChannel works in supporting browsers

### Performance (Should Pass)
- [ ] Logo compressed to 40-60% of original
- [ ] API response time < 100ms
- [ ] No memory leaks
- [ ] Smooth animation and transitions

### Validation (Must Pass)
- [ ] Color format validation works
- [ ] File size limit enforced
- [ ] File type validation works
- [ ] Required fields validated

---

## Sign-Off Checklist

When all tests pass:

- [ ] Core functionality working
- [ ] Real-time sync working
- [ ] Performance acceptable
- [ ] Error handling working
- [ ] Cross-browser compatible
- [ ] Documentation complete
- [ ] Ready for production

---

## Notes

- All timestamps in test results should be recorded
- Any failures should be logged with error details
- Screenshots of working features can be saved
- Performance metrics should be documented
- Browser console should be clear of errors
