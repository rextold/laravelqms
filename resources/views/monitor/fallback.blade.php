<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display - System Starting</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            height: 100vh;
            overflow: hidden;
        }
        .fallback-box { 
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 3rem 4rem; 
            border-radius: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
        }
        h1 { 
            color: #3b82f6; 
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        p { 
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 2rem;
            padding: 1rem 1.5rem;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 0.75rem;
            color: #60a5fa;
        }
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(59, 130, 246, 0.3);
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .error-message {
            padding: 1rem;
            margin-top: 1rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 0.5rem;
            color: #fca5a5;
            display: none;
        }
        .network-status {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 0.75rem 1rem;
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.4);
            border-radius: 0.5rem;
            color: #86efac;
            font-size: 0.875rem;
            z-index: 100;
        }
        .network-status.offline {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }
    </style>
</head>
<body>
    <div class="network-status" id="networkStatus">
        <i class="fas fa-wifi"></i> Online
    </div>

    <div class="fallback-box">
        <h1><i class="fas fa-calendar-check"></i></h1>
        <h1>Queue Display System</h1>
        <p id="message">Initializing system...</p>
        
        <div class="status-indicator">
            <div class="spinner"></div>
            <span id="statusText">Connecting to server</span>
        </div>

        <div class="error-message" id="errorMessage"></div>
    </div>

    <script>
        const fallbackBox = document.querySelector('.fallback-box');
        const messageEl = document.getElementById('message');
        const statusText = document.getElementById('statusText');
        const errorMessage = document.getElementById('errorMessage');
        const networkStatus = document.getElementById('networkStatus');

        // Check network status
        function updateNetworkStatus() {
            const isOnline = navigator.onLine;
            networkStatus.classList.toggle('offline', !isOnline);
            networkStatus.innerHTML = isOnline 
                ? '<i class="fas fa-wifi"></i> Online' 
                : '<i class="fas fa-wifi-slash"></i> Offline';
        }

        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);
        updateNetworkStatus();

        // Try to load organization or show helpful message
        async function initializeDisplay() {
            const error = '{{ $error ?? null }}';
            
            if (error) {
                messageEl.textContent = 'Organization not found';
                statusText.textContent = 'Please check the URL or contact your administrator';
                errorMessage.style.display = 'block';
                errorMessage.textContent = error;
                fallbackBox.style.opacity = '1';
                return;
            }

            // Try to auto-redirect to default organization
            try {
                const response = await fetch('/monitor');
                if (response.ok) {
                    window.location.href = '/monitor';
                    return;
                }
            } catch (e) {
                console.warn('Could not auto-redirect:', e);
            }

            messageEl.textContent = 'No organization configured';
            statusText.textContent = 'Waiting for administrator setup...';
            fallbackBox.style.opacity = '1';

            // Keep checking for organization every 5 seconds
            setInterval(async () => {
                try {
                    const response = await fetch('/monitor');
                    if (response.ok) {
                        window.location.href = '/monitor';
                    }
                } catch (e) {
                    // Continue waiting
                }
            }, 5000);
        }

        // Start initialization when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeDisplay);
        } else {
            initializeDisplay();
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
