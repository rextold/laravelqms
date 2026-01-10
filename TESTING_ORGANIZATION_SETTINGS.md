# Organization Settings Form Testing Guide

## Quick Test Steps

### Step 1: Open the Settings Page
1. Navigate to your organization admin dashboard
2. Click on "Organization Settings"
3. You should see the settings form with all fields

### Step 2: Make Changes
1. Change the **Organization Name** to something different
2. Click on one or more **Color Pickers** and select a new color
   - Notice the hex value updates in the text field below
   - Notice the live preview updates in real-time
3. Change the **Queue Number Digits** if desired
4. Leave logo unchanged (optional)

### Step 3: Monitor the Browser Console
1. Open Developer Tools: **F12** or **Right-click → Inspect**
2. Go to the **Console** tab
3. When you click "Save Settings", you'll see logged form data

### Step 4: Submit the Form
1. Click the **Save Settings** button
2. Watch for the success/error message at the top

## Expected Results

### On Success ✅
- **Console Output**: Should show:
  ```
  Form data being sent:
    organization_name: [your org name]
    primary_color: #XXXXXX
    secondary_color: #XXXXXX
    accent_color: #XXXXXX
    text_color: #XXXXXX
    queue_number_digits: [1-10]
    ...
  ```
- **Toast Message**: Green success notification at top
- **Settings Applied**: All changes visible in real-time
- **Other Pages**: Within 3 seconds, other open pages should show the new colors
- **Browser Console**: Should show successful response with settings data

### On Validation Error ❌
- **Console Output**: Will still show what data was sent
- **Toast Message**: Red error notification listing specific field errors
- **Error Format**: Each field error shown as a bullet point
  - Example: `• The primary color field is required.`
  - Example: `• The text color field must be a valid color hex value.`

## Debugging Tips

### If you get validation errors:

1. **Check Console Output** (F12 → Console)
   - See exactly what values were sent
   - Look for null/undefined values

2. **Verify Field Values**
   - Make sure you picked colors (should be 6-char hex codes)
   - Make sure organization name isn't empty
   - Make sure queue digits is a number between 1-10

3. **Check Network Tab**
   - Open F12 → Network tab
   - Look for the PUT request to `/[org-code]/admin/organization-settings`
   - Click on it and check the response - see full error list

### If nothing happens when you click Save:
1. Check browser console for JavaScript errors
2. Verify the organization code is correct in the URL
3. Make sure you're authenticated and have admin permissions
4. Clear browser cache (Ctrl+Shift+Delete)

## Form Fields Explained

| Field | Type | Required | Format | Example |
|-------|------|----------|--------|---------|
| Organization Name | Text | Yes | 1-255 chars | "Hospital A" |
| Phone Number | Text | No | Any format | "+1-555-0123" |
| Email Address | Email | No | Valid email | "contact@org.com" |
| Address | Textarea | No | 1-500 chars | "123 Main St" |
| Primary Color | Color | Yes | Hex #XXXXXX | #3B82F6 |
| Secondary Color | Color | Yes | Hex #XXXXXX | #10B981 |
| Accent Color | Color | Yes | Hex #XXXXXX | #F59E0B |
| Text Color | Color | Yes | Hex #XXXXXX | #1F2937 |
| Queue Digits | Select | Yes | 1-10 | 4 |
| Logo | File | No | PNG/JPG, <2MB | logo.png |

## Real-time Sync Behavior

After successful save:
1. Current page shows success message
2. Settings update immediately on current page
3. **Within 3 seconds**: All other open pages show new colors
4. **Across tabs**: If you have multiple browser tabs open, all sync automatically
5. **Monitor displays**: If you have kiosk/monitor displays open, they'll update too

## Color Preview

The **Live Preview** section shows how your colors will look together:
- **Background**: Gradient of Primary + Secondary colors
- **Title**: Using Text color
- **Button**: Using Accent color
- **Updates in real-time** as you change colors

## File Uploads

Logo upload is optional:
- Formats: PNG, JPG, GIF
- Maximum size: 2MB
- Will be automatically compressed to max 400x400px
- Compression reduces file size by 40-60%

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Colors not submitting | Make sure you selected them (not just viewing picker) |
| Validation errors for colors | Ensure colors are being picked (check console log) |
| Settings not syncing to other pages | Refresh the other pages; they poll every 3 seconds |
| Logo not uploading | Check file size (<2MB) and format (PNG/JPG/GIF) |
| Form looks broken | Clear cache (Ctrl+Shift+Delete) and reload |
| Can't access page | Verify you're an admin in that organization |

## Advanced: Console Commands

You can manually test the form submission in console:

```javascript
// Check what values are in the form
const formData = new FormData(document.getElementById('settingsForm'));
for (let [key, value] of formData.entries()) {
  console.log(`${key}: ${value}`);
}

// Manually trigger color update
document.getElementById('primaryColor').value = '#FF0000';
document.getElementById('primaryColor').dispatchEvent(new Event('input'));

// Check if settingsSync is working
console.log(window.settingsSync);
window.settingsSync.fetchAndApply();
```

## Final Validation Checklist

- [ ] Form page loads without JavaScript errors
- [ ] Organization name field is populated with current value
- [ ] All 4 color pickers show current colors
- [ ] Queue digits dropdown has current value selected
- [ ] Changing colors updates hex display below
- [ ] Live preview updates when colors change
- [ ] Save button is visible and clickable
- [ ] Console shows form data when submitting
- [ ] Success message appears (or error list if validation fails)
- [ ] Settings update in real-time
- [ ] Other pages sync within 3 seconds
