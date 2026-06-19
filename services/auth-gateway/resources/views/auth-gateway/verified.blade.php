<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Check Passed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body style="margin:0; font-family:Arial, sans-serif; background:#f3f4f6; color:#111827;">

<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;">
    <main
        x-data="{ loading: false }"
        style="width:100%; max-width:420px; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; padding:28px; box-shadow:0 10px 30px rgba(15,23,42,.08);"
    >
        <div style="display:inline-flex; align-items:center; gap:8px; background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; border-radius:999px; padding:7px 12px; font-size:13px; font-weight:700; margin-bottom:18px;">
            <span>✓</span>
            <span>Security check passed</span>
        </div>

        <h1 style="margin:0 0 8px; font-size:24px; line-height:1.2; font-weight:800;">
            Continue to BFRN
        </h1>

        <p style="margin:0 0 22px; color:#4b5563; font-size:14px; line-height:1.55;">
            Your email, device, and network check passed. Continue to the normal BFRN login to complete password and business unit access.
        </p>

        <a
            href="{{ $bfrnLoginUrl }}"
            x-on:click="loading = true"
            style="display:flex; align-items:center; justify-content:center; width:100%; height:46px; border-radius:10px; background:#2563eb; color:#ffffff; text-decoration:none; font-size:14px; font-weight:800;"
        >
            <span x-show="!loading">Continue to BFRN Login</span>
            <span x-show="loading">Opening BFRN...</span>
        </a>

        <p style="margin:16px 0 0; color:#6b7280; font-size:12px; line-height:1.45;">
            BFRN remains responsible for password login, business unit selection, permissions, and dashboard access.
        </p>
    </main>
</div>

</body>
</html>
