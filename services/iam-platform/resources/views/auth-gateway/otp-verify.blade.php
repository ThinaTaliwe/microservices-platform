<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Login</title>

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background: #e2e8f0;
            color: #111827;
            font-family: Inter, Arial, sans-serif;
        }

        .card {
            width: min(420px, calc(100vw - 32px));
            padding: 32px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .18);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        p {
            margin: 0 0 24px;
            color: #64748b;
            line-height: 1.5;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 8px;
            text-align: center;
        }

        button {
            width: 100%;
            margin-top: 16px;
            padding: 13px 16px;
            border: 0;
            border-radius: 10px;
            background: #2563eb;
            color: #ffffff;
            font-weight: 800;
            cursor: pointer;
        }

        .error {
            margin-bottom: 16px;
            padding: 12px;
            border-radius: 10px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 14px;
        }
    </style>
</head>
<body>
<main class="card">
    <h1>Verify your login</h1>
    <p>Enter the six-digit code sent to your email address.</p>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('otp.verify.submit') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <label for="code">Verification code</label>

        <input
            id="code"
            name="code"
            type="text"
            inputmode="numeric"
            pattern="[0-9]{6}"
            maxlength="6"
            autocomplete="one-time-code"
            required
            autofocus
        >

        <button type="submit">Verify and continue</button>
    </form>
</main>
</body>
</html>
