<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSP Test</title>
    
    <!-- Include the CSP meta tags -->
    @include('components.counter.csrf-meta')
    
    <style nonce="{{ session('csp_nonce') }}">
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .test-result { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>CSP Compliance Test</h1>
    
    <div id="test-results">
        <div class="test-result">
            <strong>CSP Nonce Test:</strong>
            <span id="csp-nonce-result">Testing...</span>
        </div>
        <div class="test-result">
            <strong>Inline Script Execution:</strong>
            <span id="script-execution-result">Testing...</span>
        </div>
        <div class="test-result">
            <strong>Inline Style Application:</strong>
            <span id="style-result">Testing...</span>
        </div>
    </div>
    
    <script nonce="{{ session('csp_nonce') }}">
        // Test CSP nonce functionality
        document.addEventListener('DOMContentLoaded', function() {
            const nonceResult = document.getElementById('csp-nonce-result');
            const scriptResult = document.getElementById('script-execution-result');
            const styleResult = document.getElementById('style-result');
            
            // Test 1: Check if CSP nonce is available
            const nonce = '{{ session("csp_nonce") }}';
            if (nonce && nonce.length > 0) {
                nonceResult.innerHTML = '<span class="success">✓ CSP nonce is available: ' + nonce.substring(0, 8) + '...</span>';
            } else {
                nonceResult.innerHTML = '<span class="error">✗ CSP nonce is not available</span>';
            }
            
            // Test 2: Check if this inline script executed
            scriptResult.innerHTML = '<span class="success">✓ Inline script with nonce executed successfully</span>';
            
            // Test 3: Check if inline styles work
            const testDiv = document.createElement('div');
            testDiv.style.color = 'blue';
            testDiv.textContent = 'Test style applied';
            document.body.appendChild(testDiv);
            
            if (testDiv.style.color === 'blue') {
                styleResult.innerHTML = '<span class="success">✓ Inline styles are working</span>';
            } else {
                styleResult.innerHTML = '<span class="error">✗ Inline styles are blocked</span>';
            }
            
            // Clean up
            document.body.removeChild(testDiv);
        });
    </script>
    
    <!-- Test script without nonce (should be blocked by CSP) -->
    <script>
        console.log('This script should be blocked by CSP');
    </script>
</body>
</html>