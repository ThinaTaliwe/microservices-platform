<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px;">
    <div style="max-width:620px; margin:auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:24px;">
        <h2 style="margin-top:0;">High Risk Login Approval Required</h2>

        <p>A login attempt requires supervisor review.</p>

        <table style="width:100%; border-collapse:collapse;">
            <tr><td><strong>Email:</strong></td><td>{{ $details['masked_email'] }}</td></tr>
            <tr><td><strong>Device:</strong></td><td>{{ $details['device'] }}</td></tr>
            <tr><td><strong>Location:</strong></td><td>{{ $details['location'] }}</td></tr>
            <tr><td><strong>Risk:</strong></td><td>{{ $details['risk'] }}</td></tr>
            <tr><td><strong>Reason:</strong></td><td>{{ $details['reason'] }}</td></tr>
        </table>

        <p style="margin-top:24px;">
            <a href="{{ $details['approve_url'] }}" style="background:#059669; color:#ffffff; padding:12px 18px; border-radius:8px; text-decoration:none; display:inline-block; margin-right:8px;">
                Approve Login
            </a>

            <a href="{{ $details['block_url'] }}" style="background:#dc2626; color:#ffffff; padding:12px 18px; border-radius:8px; text-decoration:none; display:inline-block; margin-right:8px;">
                Block Login
            </a>

            <a href="{{ $details['supervisor_url'] }}" style="background:#2563eb; color:#ffffff; padding:12px 18px; border-radius:8px; text-decoration:none; display:inline-block;">
                Review Details
            </a>
        </p>

        <p style="font-size:12px; color:#6b7280;">
            This approval request expires automatically.
        </p>
    </div>
</body>
</html>
