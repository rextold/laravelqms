<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Monitor - System Unavailable</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="text-center text-white">
        <div class="mb-8">
            <div class="w-24 h-24 mx-auto mb-6 bg-white bg-opacity-20 rounded-full flex items-center justify-center pulse-animation">
                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold mb-4">Queue Monitor</h1>
            <p class="text-xl mb-6">System Temporarily Unavailable</p>
            <p class="text-lg opacity-80 mb-8">No active organizations found. Please contact your system administrator.</p>
            
            <div class="bg-white bg-opacity-10 rounded-lg p-6 max-w-md mx-auto">
                <h2 class="text-lg font-semibold mb-3">What to do:</h2>
                <ul class="text-left space-y-2">
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-white rounded-full mr-3"></span>
                        Contact your system administrator
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-white rounded-full mr-3"></span>
                        Ensure organizations are properly configured
                    </li>
                    <li class="flex items-center">
                        <span class="w-2 h-2 bg-white rounded-full mr-3"></span>
                        Check system status
                    </li>
                </ul>
            </div>
            
            <div class="mt-8">
                <button onclick="window.location.reload()" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-semibold py-2 px-6 rounded-lg transition duration-300">
                    Refresh Page
                </button>
            </div>
        </div>
        
        <div class="text-sm opacity-60">
            <p>Laravel Queue Management System</p>
            <p class="mt-1">Monitor Display - Fallback Mode</p>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds to check if system is back online
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const refreshButton = document.querySelector('button');
            refreshButton.addEventListener('click', function() {
                this.innerHTML = 'Refreshing...';
                this.disabled = true;
            });
        });
    </script>
</body>
</html>