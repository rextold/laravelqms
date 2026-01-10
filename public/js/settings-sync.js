/**
 * Real-time Settings Sync
 * Handles automatic updates for brand colors, logo, organization name across all pages
 */

class SettingsSync {
    constructor(organizationCode, updateInterval = 5000) {
        this.orgCode = organizationCode;
        this.updateInterval = updateInterval;
        this.settingsUrl = `/${organizationCode}/admin/organization-settings/api/get`;
        this.cachedSettings = {};
        this.pollInterval = null;
        this.init();
    }

    init() {
        // Start polling for updates
        this.fetchSettings();
        this.pollInterval = setInterval(() => this.fetchSettings(), this.updateInterval);
    }

    fetchSettings() {
        fetch(this.settingsUrl)
            .then(response => {
                if (!response.ok) throw new Error('Failed to fetch settings');
                return response.json();
            })
            .then(data => {
                this.applySettings(data);
                this.cachedSettings = data;
            })
            .catch(error => console.error('Settings sync error:', error));
    }

    applySettings(settings) {
        // Update CSS variables for colors
        this.updateCSSVariable('--primary', settings.primary_color);
        this.updateCSSVariable('--secondary', settings.secondary_color);
        this.updateCSSVariable('--accent', settings.accent_color);
        this.updateCSSVariable('--text', settings.text_color);
        
        // Kiosk uses different variable names
        this.updateCSSVariable('--primary-color', settings.primary_color);
        this.updateCSSVariable('--secondary-color', settings.secondary_color);
        this.updateCSSVariable('--accent-color', settings.accent_color);
        this.updateCSSVariable('--text-color', settings.text_color);

        // Update organization name in all places
        if (settings.organization_name) {
            this.updateOrgName(settings.organization_name);
        }

        // Update logo in all places
        if (settings.company_logo) {
            this.updateLogo(settings.company_logo);
        }
    }

    updateCSSVariable(varName, value) {
        if (value) {
            document.documentElement.style.setProperty(varName, value);
        }
    }

    updateOrgName(orgName) {
        // Update in sidebar/header
        document.querySelectorAll('[data-org-name]').forEach(el => {
            el.textContent = orgName;
        });

        // Update in page title if exists
        const titleEl = document.querySelector('[data-page-title]');
        if (titleEl && titleEl.innerHTML.includes('Organization Settings')) {
            titleEl.innerHTML = titleEl.innerHTML.replace(
                /Organization Settings for .+/,
                `Organization Settings for ${orgName}`
            );
        }
    }

    updateLogo(logoUrl) {
        // Update logo images
        document.querySelectorAll('[data-org-logo]').forEach(img => {
            if (img.src !== logoUrl) {
                img.src = logoUrl;
            }
        });

        // Update logo in header/sidebar
        const logoElements = document.querySelectorAll('.org-logo, [data-logo]');
        logoElements.forEach(el => {
            if (el.tagName === 'IMG') {
                if (el.src !== logoUrl) {
                    el.src = logoUrl;
                }
            } else {
                el.style.backgroundImage = `url('${logoUrl}')`;
            }
        });
    }

    fetchAndApply() {
        // Public method to manually fetch and apply settings immediately
        this.fetchSettings();
    }

    destroy() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
        }
    }
}

// Auto-initialize on page load if orgCode is available
document.addEventListener('DOMContentLoaded', () => {
    const orgCode = document.querySelector('[data-organization-code]')?.getAttribute('data-organization-code');
    if (orgCode && !window.settingsSync) {
        window.settingsSync = new SettingsSync(orgCode);
    }
});
