<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging out...</title>
</head>
<body>
    <form id="logoutForm" action="{{ route('logout') }}" method="POST">
        @csrf
    </form>
    <script>
        document.getElementById('logoutForm').submit();
    </script>
</body>
</html>
