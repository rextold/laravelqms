<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Test</title>
    
    <style nonce="{{ session('csp_nonce') }}">
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .test-result { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
        .test-section { margin: 20px 0; padding: 15px; border: 2px solid #eee; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>CSRF Token Test</h1>
    
    <div class="test-section">
        <h2>CSRF Token Status</h2>
        <div id="csrf-status" class="test-result">
            Checking CSRF token...
        </div>
    </div>
    
    <div class="test-section">
        <h2>AJAX CSRF Test</h2>
        <button id="testAjaxBtn" class="test-result">Test AJAX with CSRF</button>
        <div id="ajax-result" class="test-result"></div>
    </div>
    
    <div class="test-section">
        <h2>Form Submission Test</h2>
        <form id="testForm" method="POST" action="/test-csrf-endpoint">
            @csrf
            <input type="text" name="test_data" value="test_value" readonly>
            <button type="submit">Test Form with CSRF</button>
        </form>
        <div id="form-result" class="test-result"></div>
    </div>
    
    <div class="test-section">
        <h2>Token Refresh Test</h2>
        <button id="refreshTokenBtn" class="test-result">Test Token Refresh</button>
        <div id="refresh-result" class="test-result"></div>
    </div>

    <script nonce="{{ session('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            // Test 1: Check CSRF token availability
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const csrfStatus = document.getElementById('csrf-status');
            
            if (csrfToken) {
                csrfStatus.innerHTML = '<span class="success">✓ CSRF token found: ' + csrfToken.substring(0, 10) + '...</span>';
            } else {
                csrfStatus.innerHTML = '<span class="error">✗ CSRF token not found in meta tag</span>';
            }
            
            // Test 2: AJAX with CSRF
            document.getElementById('testAjaxBtn').addEventListener('click', function() {
                const ajaxResult = document.getElementById('ajax-result');
                ajaxResult.innerHTML = 'Testing AJAX request...';
                
                fetch('/refresh-csrf', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken || ''
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    } else {
                        throw new Error('HTTP ' + response.status);
                    }
                })
                .then(data => {
                    if (data.token) {
                        ajaxResult.innerHTML = '<span class="success">✓ AJAX request successful, new token: ' + data.token.substring(0, 10) + '...</span>';
                    } else {
                        ajaxResult.innerHTML = '<span class="warning">⚠ AJAX request completed but no token returned</span>';
                    }
                })
                .catch(error => {
                    ajaxResult.innerHTML = '<span class="error">✗ AJAX request failed: ' + error.message + '</span>';
                });
            });
            
            // Test 3: Form submission
            document.getElementById('testForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formResult = document.getElementById('form-result');
                formResult.innerHTML = 'Testing form submission...';
                
                const formData = new FormData(this);
                
                fetch('/test-csrf-endpoint', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        formResult.innerHTML = '<span class="success">✓ Form submission successful</span>';
                    } else if (response.status === 419) {
                        formResult.innerHTML = '<span class="error">✗ CSRF token mismatch (419)</span>';
                    } else if (response.status === 403) {
                        formResult.innerHTML = '<span class="error">✗ Forbidden (403) - CSRF issue</span>';
                    } else {
                        formResult.innerHTML = '<span class="error">✗ Form submission failed: HTTP ' + response.status + '</span>';
                    }
                })
                .catch(error => {
                    formResult.innerHTML = '<span class="error">✗ Form submission error: ' + error.message + '</span>';
                });
            });
            
            // Test 4: Token refresh
            document.getElementById('refreshTokenBtn').addEventListener('click', function() {
                const refreshResult = document.getElementById('refresh-result');
                refreshResult.innerHTML = 'Testing token refresh...';
                
                // Simulate the refresh logic from counter.blade.php
                fetch('/refresh-csrf', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.token) {
                        // Update meta tag
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                        refreshResult.innerHTML = '<span class="success">✓ Token refreshed successfully: ' + data.token.substring(0, 10) + '...</span>';
                    } else {
                        refreshResult.innerHTML = '<span class="warning">⚠ Refresh completed but no token returned</span>';
                    }
                })
                .catch(error => {
                    refreshResult.innerHTML = '<span class="error">✗ Token refresh failed: ' + error.message + '</span>';
                });
            });
        });
    </script>
</body>
</html>