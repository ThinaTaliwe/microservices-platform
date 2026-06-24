<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth Gateway</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">    @if(session("status"))        <meta http-equiv="refresh" content="5">    @endif

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            background: #cad4ec;
            color: #111827;
            display: grid;
            place-items: center;
        }

        .auth-card {
            width: min(440px, calc(100vw - 32px));
            background: #ffffff;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 24px 80px rgba(0,0,0,.35);
        }

        .title {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 6px;
            text-align: center;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        label {
            font-weight: 700;
            font-size: 14px;
            display: block;
            margin-bottom: 8px;
        }

        input[type="email"] {
            width: 100%;
            box-sizing: border-box;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
        }

        input[type="email"]:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        }

        button {
            width: 100%;
            margin-top: 16px;
            border: 0;
            border-radius: 12px;
            background: #2563eb;
            color: white;
            padding: 13px 16px;
            font-weight: 800;
            cursor: pointer;
        }

        .alert {
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .footnote {
            margin-top: 18px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }
    </style>

</head>
<body>
    <main class="auth-card">

        <div class="my-2">
            <h1 class="title">Auto Gateway</h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="/login" id="authGatewayForm">
            @csrf

            <label for="email">Email address</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                autocomplete="email"
                required
                autofocus
            >

            <input type="hidden" name="timezone" id="timezone">
            <input type="hidden" name="screen" id="screen">
            <input type="hidden" name="platform" id="platform">
            <input type="hidden" name="language" id="language">

            <button type="submit">Continue Securely</button>
        </form>

        {{-- <div class="footnote">
            Device and network details are captured for security review and supervisor approval when needed.
        </div> --}}
    </main>

    <script>
        document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        document.getElementById('screen').value = `${window.screen.width}x${window.screen.height}`;
        document.getElementById('platform').value = navigator.platform || '';
        document.getElementById('language').value = navigator.language || '';
    </script>

</body>
</html>
