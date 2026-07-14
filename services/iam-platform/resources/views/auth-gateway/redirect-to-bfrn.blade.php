<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to BFRN</title>
    <meta http-equiv="refresh" content="1;url={{ $bfrnLoginUrl }}">
    <script>
        document.cookie = 'laravel_session=; Max-Age=0; path=/; SameSite=Lax';
        document.cookie = 'XSRF-TOKEN=; Max-Age=0; path=/; SameSite=Lax';

        setTimeout(function () {
            window.location.replace(@json($bfrnLoginUrl));
        }, 500);
    </script>
</head>
<body style="font-family: Arial, sans-serif; background:#0f172a; color:#fff; display:grid; place-items:center; min-height:100vh;">
    <div>
        <h2>Security check passed</h2>
        <p>Redirecting to BFRN login...</p>
    </div>
</body>
</html>
