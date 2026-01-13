/**
 * Settings Sync - Real-time synchronization of organization settings
 * across all pages without requiring page refresh
 * Handles brand colors, logos, and organization name updates
 */

class SettingsSync {
    constructor(organizationCode, updateInterval = 3000) {
        this.orgCode = organizationCode;
        this.updateInterval = updateInterval;
        this.settingsUrl = `/${organizationCode}/api/settings`;
        this.cachedSettings = {};
        this.pollInterval = null;
        this.lastCheckTime = 0;
        this.init();
    }

    init() {
        // Start polling for updates
        this.fetchSettings();
        this.pollInterval = setInterval(() => this.fetchSettings(), this.updateInterval);
        
        // Listen for broadcast messages from other tabs
        this.setupBroadcastListener();
    }

    async fetchSettings(retryCount = 0) {
        // Warn if orgCode is 'default'
        if (this.orgCode === 'default') {
            console.warn('Organization code is "default". This may not be valid for API access.');
        }            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(this.settingsUrl, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json',
                    },
                });

                // Check for authentication errors and show modal if needed
                if (window.handleAuthError && window.handleAuthError(response)) {
                    return;
                }

                if (response.status === 403 && retryCount < 2) {
                    // Try to refresh CSRF token and retry
                    console.warn('403 Forbidden on settings fetch, retrying...');
                    setTimeout(() => this.fetchSettings(retryCount + 1), 1000);
                    return;
                }

                if (!response.ok) {
                    console.warn('Failed to fetch settings, status:', response.status);
                    return;
                }
            const data = await response.json();
            if (data) {
                if (JSON.stringify(data) !== JSON.stringify(this.cachedSettings)) {
                    this.applySettings(data);
                    this.cachedSettings = { ...data };
                    console.log('Settings updated:', data);
                }
            }
        } catch (error) {
            console.warn('Settings sync error:', error.message);
        }
    }

    applySettings(settings) {
        // Update CSS variables for colors - multiple variants for compatibility
        this.updateCSSVariable('--primary', settings.primary_color);
        this.updateCSSVariable('--primary-color', settings.primary_color);
        this.updateCSSVariable('--secondary', settings.secondary_color);
        this.updateCSSVariable('--secondary-color', settings.secondary_color);
        this.updateCSSVariable('--accent', settings.accent_color);
        this.updateCSSVariable('--accent-color', settings.accent_color);
        this.updateCSSVariable('--text', settings.text_color);
        this.updateCSSVariable('--text-color', settings.text_color);

        // Update organization name in all places
        if (settings.organization_name) {
            this.updateOrgName(settings.organization_name);
        }

        // Update logo in all places
        if (settings.company_logo) {
            this.updateLogo(settings.company_logo);
        }

        // Apply theme colors to elements with data attributes
        this.applyThemeToElements(settings);
    }

    updateCSSVariable(varName, value) {
        if (value) {
            document.documentElement.style.setProperty(varName, value);
        }
    }

    applyThemeToElements(settings) {
        // Update elements with data-theme attribute
        document.querySelectorAll('[data-theme="bg-primary"]').forEach(el => {
            if (settings.primary_color) {
                el.style.backgroundColor = settings.primary_color;
            }
        });

        document.querySelectorAll('[data-theme="bg-secondary"]').forEach(el => {
            if (settings.secondary_color) {
                el.style.backgroundColor = settings.secondary_color;
            }
        });

        document.querySelectorAll('[data-theme="bg-accent"]').forEach(el => {
            if (settings.accent_color) {
                el.style.backgroundColor = settings.accent_color;
            }
        });

        document.querySelectorAll('[data-theme="text"]').forEach(el => {
            if (settings.text_color) {
                el.style.color = settings.text_color;
            }
        });

        document.querySelectorAll('[data-theme="gradient"]').forEach(el => {
            if (settings.primary_color && settings.secondary_color) {
                el.style.background = `linear-gradient(135deg, ${settings.primary_color}, ${settings.secondary_color})`;
            }
        });
    }

    updateOrgName(orgName) {
        // Update in sidebar/header with data-org-name attribute
        document.querySelectorAll('[data-org-name]').forEach(el => {
            el.textContent = orgName;
        });

        // Update page title
        const titleEl = document.querySelector('[data-page-title]');
        if (titleEl) {
            // Only update if it contains "Organization Settings"
            if (titleEl.innerHTML.includes('Organization Settings')) {
                titleEl.innerHTML = titleEl.innerHTML.replace(
                    /Organization Settings for .+/,
                    `Organization Settings for ${orgName}`
                );
            }
        }

        // Update browser tab title
        const pageTitle = document.querySelector('title');
        if (pageTitle && pageTitle.textContent.includes('Organization Settings')) {
            const basePage = pageTitle.textContent.split(' - ')[1];
            pageTitle.textContent = orgName + (basePage ? ` - ${basePage}` : '');
        }
    }

    updateLogo(logoUrl) {
        if (!logoUrl) return;

        // Ensure URL is properly formatted
        const fullLogoUrl = logoUrl.startsWith('/') || logoUrl.startsWith('http') 
            ? logoUrl 
            : `/storage/${logoUrl}`;

        // Update logo images with data-org-logo attribute
        document.querySelectorAll('[data-org-logo]').forEach(el => {
            if (el.tagName === 'IMG') {
                el.src = fullLogoUrl;
                el.onerror = () => console.warn('Failed to load logo:', fullLogoUrl);
            } else if (el.tagName === 'DIV') {
                // Check if div contains an img tag
                const img = el.querySelector('img');
                if (img) {
                    img.src = fullLogoUrl;
                    img.onerror = () => console.warn('Failed to load logo in div:', fullLogoUrl);
                } else {
                    // Create img tag if it doesn't exist
                    el.innerHTML = `<img src="${fullLogoUrl}" alt="Logo" data-org-logo style="width: 100%; height: 100%; object-fit: contain;">`;
                }
            }
        });

        // Update legacy org-logo class elements
        document.querySelectorAll('.org-logo').forEach(el => {
            if (el.tagName === 'IMG') {
                el.src = fullLogoUrl;
            } else {
                el.style.backgroundImage = `url('${fullLogoUrl}')`;
            }
        });
    }

    setupBroadcastListener() {
        // Listen for custom events from form submissions
        window.addEventListener('organizationSettingsUpdated', (event) => {
            const settings = event.detail?.settings;
            if (settings) {
                this.cachedSettings = { ...settings };
                this.applySettings(settings);
            }
        });

        // Try BroadcastChannel for cross-tab communication (modern browsers)
        if (typeof BroadcastChannel !== 'undefined') {
            try {
                const channel = new BroadcastChannel(`org-settings-${this.orgCode}`);
                channel.addEventListener('message', (event) => {
                    if (event.data.type === 'SETTINGS_UPDATED') {
                        this.cachedSettings = { ...event.data.settings };
                        this.applySettings(event.data.settings);
                        console.log('Settings updated from another tab');
                    }
                });
            } catch (e) {
                console.warn('BroadcastChannel not available:', e.message);
            }
        }
    }

    // Public method to trigger immediate update
    fetchAndApply() {
        this.fetchSettings();
    }

    // Public method to broadcast update to other tabs
    broadcastUpdate(settings) {
        try {
            if (typeof BroadcastChannel !== 'undefined') {
                const channel = new BroadcastChannel(`org-settings-${this.orgCode}`);
                channel.postMessage({
                    type: 'SETTINGS_UPDATED',
                    settings: settings
                });
            }
        } catch (e) {
            console.warn('Failed to broadcast update:', e.message);
        }

        // Also dispatch custom event
        window.dispatchEvent(new CustomEvent('organizationSettingsUpdated', {
            detail: { settings: settings }
        }));
    }

    destroy() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }
    }
}

// Auto-initialize on page load if orgCode is available
document.addEventListener('DOMContentLoaded', () => {
    // Try to get orgCode from data attribute first
    let orgCode = document.querySelector('[data-organization-code]')?.getAttribute('data-organization-code');
    
    // If not found, extract from URL path
    if (!orgCode) {
        const pathParts = window.location.pathname.split('/').filter(p => p);
        // First path segment is usually the org code (unless it's 'login', 'superadmin', etc.)
        if (pathParts.length > 0 && !['login', 'superadmin', 'logout'].includes(pathParts[0])) {
            orgCode = pathParts[0];
        }
    }

    if (orgCode && !window.settingsSync) {
        window.settingsSync = new SettingsSync(orgCode);
    }
});
