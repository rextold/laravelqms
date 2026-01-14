/**
 * Global Resilience Layer
 * Ensures application pages remain stable during network/server issues
 */

(function() {
    'use strict';

    // Track if we're on a public display
    const isPublicDisplay = () => {
        const path = window.location.pathname;
        return path.includes('/kiosk') || path.includes('/monitor');
    };

    // Graceful degradation for critical pages
    const isCounterPanel = () => window.location.pathname.includes('/counter/panel');
    const isAdminPanel = () => document.body.classList.contains('bg-gray-50');

    // Network state tracking
    let isOnline = navigator.onLine;
    
    window.addEventListener('online', () => {
        isOnline = true;
        console.log('[Resilience] Network restored');
        // Trigger refresh on all pages
        if (window.refreshData) window.refreshData();
        if (window.fetchData) window.fetchData();
        if (window.refreshCounters) window.refreshCounters();
    });

    window.addEventListener('offline', () => {
        isOnline = false;
        console.warn('[Resilience] Network disconnected - using cached data');
    });

    // Page visibility tracking
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            console.log('[Resilience] Page restored from hidden state');
            // Trigger refresh functions if available
            if (window.refreshData) window.refreshData();
            if (window.fetchData) window.fetchData();
            if (window.refreshCounters) window.refreshCounters();
            if (window.refreshMonitorData) window.refreshMonitorData();
        }
    });

    // Automatic data refresh with exponential backoff on failure
    class AutoRefresh {
        constructor(fn, interval = 5000) {
            this.fn = fn;
            this.baseInterval = interval;
            this.currentInterval = interval;
            this.maxInterval = 60000;
            this.failureCount = 0;
            this.maxFailures = 3;
        }

        start() {
            this.timer = setInterval(() => this.execute(), this.currentInterval);
        }

        async execute() {
            try {
                if (isOnline) {
                    await this.fn();
                    this.reset();
                }
            } catch (error) {
                this.handleFailure(error);
            }
        }

        handleFailure(error) {
            this.failureCount++;
            console.warn(`[Resilience] Refresh failed (${this.failureCount}/${this.maxFailures}):`, error);
            
            // Increase interval with exponential backoff
            if (this.failureCount >= this.maxFailures) {
                this.currentInterval = Math.min(this.currentInterval * 1.5, this.maxInterval);
                this.failureCount = 0;
            }
        }

        reset() {
            this.failureCount = 0;
            this.currentInterval = this.baseInterval;
        }

        stop() {
            clearInterval(this.timer);
        }
    }

    // Expose AutoRefresh for pages to use
    window.AutoRefresh = AutoRefresh;

    // Performance monitoring
    class PerformanceMonitor {
        constructor() {
            this.metrics = {};
        }

        startTimer(key) {
            this.metrics[key] = { start: performance.now() };
        }

        endTimer(key) {
            if (this.metrics[key]) {
                this.metrics[key].duration = performance.now() - this.metrics[key].start;
                console.log(`[Perf] ${key}: ${this.metrics[key].duration.toFixed(2)}ms`);
            }
        }

        logNavTiming() {
            const nav = performance.getEntriesByType('navigation')[0];
            if (nav) {
                console.log(`[Perf] Page load: ${nav.loadEventEnd - nav.fetchStart}ms`);
            }
        }
    }

    window.PerformanceMonitor = PerformanceMonitor;

    // Log resilience layer initialization
    console.log('[Resilience] Global resilience layer initialized');
    if (isPublicDisplay()) {
        console.log('[Resilience] Running on public display - maximum fault tolerance');
    }
})();