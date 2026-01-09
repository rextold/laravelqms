<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-red-500 via-orange-500 to-yellow-500 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">
        <div class="mb-6">
            <i class="fas fa-lock text-red-600 text-6xl mb-4"></i>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Access Denied</h1>
            <p class="text-gray-600 text-lg">403 - Forbidden</p>
        </div>

        <p class="text-gray-700 mb-6">
            You don't have permission to access this page. This view is restricted to counter/teller accounts.
        </p>

        <div class="flex flex-col gap-3">
            <a href="{{ route('login') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-sign-in-alt mr-2"></i>Back to Login
            </a>
            <a href="javascript:history.back()" class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Go Back
            </a>
        </div>

        <p class="text-xs text-gray-500 mt-6">
            If you believe this is an error, please contact your administrator.
        </p>
    </div>
</body>
</html>
