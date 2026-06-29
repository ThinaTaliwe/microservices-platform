<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm border-0 w-100" style="max-width:420px;">
        <div class="card-body p-4">

            {{-- <h1 class="h4 fw-bold mb-1 text-center">BFRN</h1> --}}
            <h1 class="h4 fw-bold mb-1 text-center">Sign_In</h1>
            {{-- <p class="text-muted text-center mb-4">Sign in to continue</p> --}}

            @if ($errors->any())
                <div class="alert alert-danger small">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/bfrn/login">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Sign In
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="/access" class="small text-decoration-none">
                    View available systems
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
