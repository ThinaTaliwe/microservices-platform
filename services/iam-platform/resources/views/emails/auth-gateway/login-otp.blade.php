<!DOCTYPE html>
<html lang="en">
<body style="font-family:Arial,sans-serif;background:#f6f7fb;padding:24px;color:#111827;">
<div style="max-width:560px;margin:auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
    <h2 style="margin:0 0 16px;">Verify your login</h2>

    <p>Enter this one-time verification code to continue securely:</p>

    <div style="margin:24px 0;padding:18px;text-align:center;background:#f1f5f9;border-radius:10px;font-size:32px;font-weight:800;letter-spacing:8px;">
        {{ $details['code'] }}
    </div>

    <p>This code expires in {{ $details['expires_minutes'] }} minutes.</p>

    <p style="margin:24px 0;">
        <a href="{{ $details['verify_url'] }}"
           style="display:inline-block;background:#2563eb;color:#ffffff;padding:12px 18px;border-radius:8px;text-decoration:none;font-weight:700;">
            Verify login
        </a>
    </p>

    <p style="font-size:13px;color:#64748b;">
        Do not share this code. IAM Platform staff will never ask you for it.
    </p>

    <p style="font-size:12px;color:#94a3b8;margin-bottom:0;">
        Login request: {{ $details['requested_at'] }}
    </p>
</div>
</body>
</html>
