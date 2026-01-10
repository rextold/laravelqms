# Quick Start Guide - Organization Settings

## For Administrators

### Accessing Organization Settings
1. Login to the admin panel
2. Navigate to: **{organization_code}/admin/organization-settings**
3. Or use the sidebar menu: **Admin → Organization Settings**

### Updating Settings

#### Organization Information
- **Organization Name** - Used everywhere in the system
- **Phone Number** - Contact information
- **Email Address** - Contact information
- **Address** - Full address
- **Queue Format** - Number of digits for queue IDs (3-6)

#### Logo
1. Click **"Choose File"** to select a logo image
2. Supported formats: PNG, JPG, GIF
3. Maximum size: 2MB
4. Recommended: 400x400px with transparent background
5. **Automatic compression:** Logo will be automatically resized and compressed for faster loading

#### Brand Colors
Set your organization's brand colors that will appear across all pages:
- **Primary Color** - Main buttons and headers
- **Secondary Color** - Gradients and accents
- **Accent Color** - Success states and highlights
- **Text Color** - Text on colored backgrounds

**Live Preview:** See changes in real-time in the preview section

#### Saving
1. Click **"Save Settings"** button
2. ✅ No page refresh - changes apply immediately!
3. All open pages will update within 3 seconds
4. Toast notification confirms successful update

---

## For Developers

### Using Theme-Aware Templates

#### 1. Display Organization Name (Auto-Updates)
```blade
<!-- Will automatically update when organization name changes -->
<h1 data-org-name>{{ $organization->organization_name }}</h1>
```

#### 2. Display Logo (Auto-Updates)
```blade
<!-- Will automatically update when logo changes -->
<img 
    data-org-logo
    src="{{ asset('storage/' . $settings->company_logo) }}" 
    alt="Logo"
    class="h-16 object-contain"
/>
```

#### 3. Use CSS Variables for Colors
```css
:root {
    /* Automatically set by SettingsSync */
    --primary-color: #667eea;      /* Primary brand color */
    --secondary-color: #764ba2;    /* Secondary brand color */
    --accent-color: #10b981;       /* Accent color */
    --text-color: #ffffff;         /* Text color */
}

/* Use in your stylesheets */
.button {
    background-color: var(--primary-color);
    color: var(--text-color);
}

.header {
    background: linear-gradient(135deg, 
        var(--primary-color), 
        var(--secondary-color));
    color: var(--text-color);
}
```

#### 4. Use Data Attributes for Backgrounds
```blade
<!-- Primary color background -->
<div data-theme="bg-primary" class="p-4 rounded">
    Content with primary background
</div>

<!-- Secondary color background -->
<div data-theme="bg-secondary" class="p-4 rounded">
    Content with secondary background
</div>

<!-- Accent color background -->
<div data-theme="bg-accent" class="p-4 rounded">
    Content with accent background
</div>

<!-- Gradient background -->
<div data-theme="gradient" class="p-4 rounded">
    Content with gradient background
</div>

<!-- Text color -->
<p data-theme="text">
    Text that changes color with theme
</p>
```

#### 5. Complete Example Component
```blade
<!-- Hero Section with Dynamic Theming -->
<div data-theme="gradient" class="py-12 rounded-lg">
    <div class="text-center">
        <!-- Logo auto-updates -->
        <img 
            data-org-logo
            src="{{ asset('storage/' . $settings->company_logo) }}" 
            alt="Logo"
            class="h-24 mx-auto mb-4 object-contain"
        />
        
        <!-- Organization name auto-updates -->
        <h1 data-org-name class="text-4xl font-bold mb-2" style="color: var(--text-color)">
            {{ $organization->organization_name }}
        </h1>
        
        <!-- Button with primary color -->
        <button class="px-6 py-3 rounded-lg font-semibold text-white" 
                style="background-color: var(--primary-color)">
            Get Started
        </button>
    </div>
</div>
```

### JavaScript Usage

#### Listen for Settings Changes
```javascript
// Listen for settings update event
window.addEventListener('organizationSettingsUpdated', function(event) {
    const settings = event.detail.settings;
    
    console.log('Colors updated:', {
        primary: settings.primary_color,
        secondary: settings.secondary_color,
        accent: settings.accent_color,
        text: settings.text_color
    });
    
    // Do something with updated settings
    updateMyComponent(settings);
});
```

#### Manually Trigger Settings Update
```javascript
// Force immediate fetch and apply
if (window.settingsSync) {
    window.settingsSync.fetchAndApply();
}
```

#### Get Current Settings
```javascript
// Access cached settings
const currentSettings = window.settingsSync.cachedSettings;

console.log('Current theme:', {
    primaryColor: currentSettings.primary_color,
    logo: currentSettings.company_logo,
    organizationName: currentSettings.organization_name
});
```

#### Broadcast Update to Other Tabs
```javascript
// Send update to other tabs/windows
if (window.settingsSync) {
    window.settingsSync.broadcastUpdate({
        primary_color: '#667eea',
        secondary_color: '#764ba2',
        accent_color: '#10b981',
        text_color: '#ffffff',
        company_logo: '/storage/logos/logo.png',
        organization_name: 'My Organization'
    });
}
```

---

## Common Use Cases

### 1. Dynamic Header with Organization Branding
```blade
<header data-theme="gradient" class="sticky top-0 shadow-lg">
    <div class="container mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <img data-org-logo src="{{ asset('storage/' . $settings->company_logo) }}" 
                 alt="Logo" class="h-10 object-contain" />
            <h1 data-org-name class="text-2xl font-bold" style="color: var(--text-color)">
                {{ $organization->organization_name }}
            </h1>
        </div>
        <nav style="color: var(--text-color)">
            <!-- Navigation links -->
        </nav>
    </div>
</header>
```

### 2. Dashboard Card with Theme Colors
```blade
<div class="bg-white rounded-lg shadow-lg p-6 border-l-4" 
     style="border-color: var(--primary-color)">
    <h3 class="text-lg font-semibold mb-2" style="color: var(--primary-color)">
        Welcome
    </h3>
    <p style="color: var(--text-color)">
        Content here
    </p>
</div>
```

### 3. CTA Button with Accent Color
```blade
<button class="px-6 py-3 rounded-lg font-semibold text-white hover:opacity-90 transition"
        style="background-color: var(--accent-color)">
    <i class="fas fa-check mr-2"></i>
    Call to Action
</button>
```

### 4. Monitor Display with Dynamic Colors
```blade
<!-- Queue displays that auto-update with colors -->
<div data-theme="bg-primary" class="flex items-center justify-center h-screen">
    <div class="text-center">
        <img data-org-logo src="{{ asset('storage/' . $settings->company_logo) }}" 
             alt="Logo" class="h-32 mx-auto mb-8 object-contain" />
        <h1 data-org-name class="text-6xl font-bold mb-8" style="color: var(--text-color)">
            {{ $organization->organization_name }}
        </h1>
        <!-- Queue numbers and info -->
    </div>
</div>
```

---

## API Endpoints

### Get Organization Settings (JSON)
```
GET /{organization_code}/api/settings
```

**Response:**
```json
{
    "organization_name": "My Organization",
    "primary_color": "#667eea",
    "secondary_color": "#764ba2",
    "accent_color": "#10b981",
    "text_color": "#ffffff",
    "company_logo": "/storage/logos/12345_logo.png",
    "company_phone": "+1 (555) 123-4567",
    "company_email": "contact@example.com",
    "company_address": "123 Main St, City, State 12345"
}
```

### Update Organization Settings
```
PUT /{organization_code}/admin/organization-settings
Content-Type: multipart/form-data

Parameters:
- organization_name (required, string, max 255)
- company_phone (optional, string)
- company_email (optional, email)
- company_address (optional, string)
- primary_color (required, hex color #RRGGBB)
- secondary_color (required, hex color #RRGGBB)
- accent_color (required, hex color #RRGGBB)
- text_color (required, hex color #RRGGBB)
- queue_number_digits (required, int 1-10)
- logo (optional, file, image, max 2MB)
```

**Response (AJAX):**
```json
{
    "success": true,
    "message": "Settings updated successfully",
    "settings": {
        "primary_color": "#667eea",
        "secondary_color": "#764ba2",
        "accent_color": "#10b981",
        "text_color": "#ffffff",
        "company_logo": "/storage/logos/12345_logo.png"
    }
}
```

---

## CSS Custom Properties Reference

All of these are automatically set by SettingsSync and available in your stylesheets:

```css
:root {
    --primary-color: <organization primary color>;
    --secondary-color: <organization secondary color>;
    --accent-color: <organization accent color>;
    --text-color: <organization text color>;
    
    /* Aliases for compatibility */
    --primary: var(--primary-color);
    --secondary: var(--secondary-color);
    --accent: var(--accent-color);
    --text: var(--text-color);
}
```

---

## Troubleshooting

### Colors aren't updating
1. Check that `public/js/settings-sync.js` is loaded (check console)
2. Verify organization code in URL is correct
3. Clear browser cache and reload
4. Check browser console (F12) for errors

### Logo not showing
1. Verify file was uploaded successfully (check browser Network tab)
2. Check that `storage/app/public/` directory exists and is writable
3. Run: `php artisan storage:link` if storage link is missing
4. Check file permissions

### Organization name not updating
1. Verify name was saved (check database)
2. Check that elements have `data-org-name` attribute
3. Check console for errors

### Form won't submit
1. Verify you have admin role
2. Check CSRF token is present in form
3. Check browser console for validation errors
4. Verify file size is under 2MB

---

## Support

For more detailed information, see:
- [BRAND_COLORS_IMPLEMENTATION.md](BRAND_COLORS_IMPLEMENTATION.md) - Complete technical documentation
- [IMPLEMENTATION_NOTES.md](IMPLEMENTATION_NOTES.md) - Implementation summary
- [Laravel Docs](https://laravel.com) - Framework documentation
