<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm border-0 w-100" style="max-width:460px;">
        <div class="card-body p-4">
            <h1 class="h4 fw-bold mb-2 text-center">Change Your Password</h1>
            <p class="text-muted text-center mb-4">
                Your account was created with a temporary password. Set your own password to continue.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger small">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/bfrn/password/change">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password</label>
                    <input type="password" name="password" class="form-control" required autofocus minlength="8">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Save Password & Continue
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
