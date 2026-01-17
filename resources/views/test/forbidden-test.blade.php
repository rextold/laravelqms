<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forbidden Issue Test</title>
    
    <style nonce="{{ session('csp_nonce') }}">
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        .test-result { margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f8f9fa; }
        .test-section { margin: 25px 0; padding: 20px; border: 2px solid #e9ecef; background: #fff; border-radius: 8px; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.9; }
        .status-indicator { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 8px; }
        .status-success { background: #28a745; }
        .status-error { background: #dc3545; }
        .status-warning { background: #ffc107; }
        .status-info { background: #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-shield-alt"></i> Forbidden Issue Test Suite</h1>
        <p>This page tests for common forbidden/403 issues in the counter system.</p>
        
        <div class="test-section">
            <h2><span class="status-indicator" id="csrf-status-indicator"></span>CSRF Token Validation</h2>
            <div id="csrf-test-result" class="test-result">
                Checking CSRF token...
            </div>
            <button id="test-csrf-btn" class="btn btn-primary">Test CSRF Token</button>
        </div>
        
        <div class="test-section">
            <h2><span class="status-indicator" id="ajax-status-indicator"></span>AJAX Request Test</h2>
            <div id="ajax-test-result" class="test-result">
                Ready to test AJAX requests...
            </div>
            <button id="test-ajax-btn" class="btn btn-success">Test AJAX Request</button>
        </div>
        
        <div class="test-section">
            <h2><span class="status-indicator" id="form-status-indicator"></span>Form Submission Test</h2>
            <div id="form-test-result" class="test-result">
                Ready to test form submission...
            </div>
            <form id="test-form" method="POST" action="/test-forbidden-endpoint">
                @csrf
                <input type="hidden" name="test_data" value="forbidden_test">
                <button type="submit" class="btn btn-danger">Test Form Submission</button>
            </form>
        </div>
        
        <div class="test-section">
            <h2><span class="status-indicator" id="counter-status-indicator"></span>Counter Panel Test</h2>
            <div id="counter-test-result" class="test-result">
                Ready to test counter functionality...
            </div>
            <button id="test-counter-btn" class="btn btn-info">Test Counter Actions</button>
        </div>
        
        <div class="test-section">
            <h2>Test Summary</h2>
            <div id="test-summary" class="test-result">
                <strong>Overall Status:</strong> <span id="overall-status">Pending</span>
                <br><br>
                <div id="issues-found"></div>
            </div>
        </div>
    </div>

    <script nonce="{{ session('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function() {
            let testResults = {
                csrf: false,
                ajax: false,
                form: false,
                counter: false
            };

            // Test 1: CSRF Token Validation
            function testCSRFToken() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const resultDiv = document.getElementById('csrf-test-result');
                const indicator = document.getElementById('csrf-status-indicator');
                
                if (csrfToken) {
                    resultDiv.innerHTML = '<span class="success"><i class="fas fa-check"></i> CSRF token found: ' + csrfToken.substring(0, 15) + '...</span>';
                    indicator.className = 'status-indicator status-success';
                    testResults.csrf = true;
                } else {
                    resultDiv.innerHTML = '<span class="error"><i class="fas fa-times"></i> CSRF token not found in meta tag</span>';
                    indicator.className = 'status-indicator status-error';
                }
                updateSummary();
            }

            // Test 2: AJAX Request Test
            function testAJAXRequest() {
                const resultDiv = document.getElementById('ajax-test-result');
                const indicator = document.getElementById('ajax-status-indicator');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                resultDiv.innerHTML = 'Testing AJAX request...';
                
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
                    } else if (response.status === 403) {
                        throw new Error('403 Forbidden - CSRF token may be invalid');
                    } else if (response.status === 419) {
                        throw new Error('419 Page Expired - CSRF token mismatch');
                    } else {
                        throw new Error('HTTP ' + response.status);
                    }
                })
                .then(data => {
                    if (data.token) {
                        resultDiv.innerHTML = '<span class="success"><i class="fas fa-check"></i> AJAX request successful</span>';
                        indicator.className = 'status-indicator status-success';
                        testResults.ajax = true;
                    } else {
                        resultDiv.innerHTML = '<span class="warning"><i class="fas fa-exclamation-triangle"></i> AJAX completed but no token returned</span>';
                        indicator.className = 'status-indicator status-warning';
                    }
                    updateSummary();
                })
                .catch(error => {
                    resultDiv.innerHTML = '<span class="error"><i class="fas fa-times"></i> AJAX failed: ' + error.message + '</span>';
                    indicator.className = 'status-indicator status-error';
                    updateSummary();
                });
            }

            // Test 3: Form Submission Test
            function testFormSubmission() {
                const resultDiv = document.getElementById('form-test-result');
                const indicator = document.getElementById('form-status-indicator');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                resultDiv.innerHTML = 'Testing form submission...';
                
                fetch('/test-forbidden-endpoint', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        test_data: 'forbidden_test',
                        _token: csrfToken
                    })
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    } else if (response.status === 403) {
                        throw new Error('403 Forbidden - Access denied');
                    } else if (response.status === 419) {
                        throw new Error('419 Page Expired - CSRF token mismatch');
                    } else if (response.status === 401) {
                        throw new Error('401 Unauthorized - Authentication required');
                    } else {
                        throw new Error('HTTP ' + response.status);
                    }
                })
                .then(data => {
                    resultDiv.innerHTML = '<span class="success"><i class="fas fa-check"></i> Form submission successful</span>';
                    indicator.className = 'status-indicator status-success';
                    testResults.form = true;
                    updateSummary();
                })
                .catch(error => {
                    resultDiv.innerHTML = '<span class="error"><i class="fas fa-times"></i> Form submission failed: ' + error.message + '</span>';
                    indicator.className = 'status-indicator status-error';
                    updateSummary();
                });
            }

            // Test 4: Counter Panel Test
            function testCounterActions() {
                const resultDiv = document.getElementById('counter-test-result');
                const indicator = document.getElementById('counter-status-indicator');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                resultDiv.innerHTML = 'Testing counter actions...';
                
                // Test a basic counter action (call-next)
                fetch('/counter/data', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken || ''
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    } else if (response.status === 403) {
                        throw new Error('403 Forbidden - Counter access denied');
                    } else if (response.status === 401) {
                        throw new Error('401 Unauthorized - Authentication required');
                    } else {
                        throw new Error('HTTP ' + response.status);
                    }
                })
                .then(data => {
                    resultDiv.innerHTML = '<span class="success"><i class="fas fa-check"></i> Counter data access successful</span>';
                    indicator.className = 'status-indicator status-success';
                    testResults.counter = true;
                    updateSummary();
                })
                .catch(error => {
                    resultDiv.innerHTML = '<span class="error"><i class="fas fa-times"></i> Counter test failed: ' + error.message + '</span>';
                    indicator.className = 'status-indicator status-error';
                    updateSummary();
                });
            }

            // Update test summary
            function updateSummary() {
                const summaryDiv = document.getElementById('overall-status');
                const issuesDiv = document.getElementById('issues-found');
                
                const passedTests = Object.values(testResults).filter(Boolean).length;
                const totalTests = Object.keys(testResults).length;
                
                if (passedTests === totalTests) {
                    summaryDiv.innerHTML = '<span class="success"><i class="fas fa-check-circle"></i> All tests passed! No forbidden issues detected.</span>';
                    issuesDiv.innerHTML = '<p class="success">✓ CSRF tokens are working properly<br>✓ AJAX requests are successful<br>✓ Form submissions are working<br>✓ Counter functionality is accessible</p>';
                } else {
                    summaryDiv.innerHTML = '<span class="error"><i class="fas fa-exclamation-triangle"></i> ' + passedTests + '/' + totalTests + ' tests passed</span>';
                    
                    let issues = '<p><strong>Issues found:</strong></p><ul>';
                    if (!testResults.csrf) issues += '<li>CSRF token issues - check meta tag and session</li>';
                    if (!testResults.ajax) issues += '<li>AJAX request failures - check CSRF headers and permissions</li>';
                    if (!testResults.form) issues += '<li>Form submission errors - check CSRF validation</li>';
                    if (!testResults.counter) issues += '<li>Counter access denied - check authentication and permissions</li>';
                    issues += '</ul>';
                    
                    issuesDiv.innerHTML = issues;
                }
            }

            // Event listeners
            document.getElementById('test-csrf-btn').addEventListener('click', testCSRFToken);
            document.getElementById('test-ajax-btn').addEventListener('click', testAJAXRequest);
            document.getElementById('test-form').addEventListener('submit', function(e) {
                e.preventDefault();
                testFormSubmission();
            });
            document.getElementById('test-counter-btn').addEventListener('click', testCounterActions);

            // Run initial CSRF test
            testCSRFToken();
        });
    </script>
</body>
</html>