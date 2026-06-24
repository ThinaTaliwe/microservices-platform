<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px;">
<div style="max-width:620px; margin:auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:24px;">
    <h2 style="margin-top:0;">BFRN Access Created</h2>

    <p>A BFRN user account has been created after supervisor approval.</p>

    <p><strong>Email:</strong> {{ $details['email'] }}</p>
    {{-- <p><strong>BFRN User ID:</strong> {{ $details['bfrn_user_id'] ?? 'Created' }}</p> --}}

    @if(!empty($details['show_password']))
        <p><strong>Temporary Password:</strong> {{ $details['password'] }}</p>
    @else
        <p><strong>Temporary Password:</strong> *********** (visible to user)</p>
    @endif

    <p>The user can access BFRN in two ways:</p>

    {{-- <ol>
        <li><strong>Email auto-login:</strong> use the Auth Gateway email login.</li>
        <li><strong>Default BFRN login:</strong> use email and password.</li>
    </ol> --}}

    <p>
        <a href="{{ $details['auth_gateway_url'] }}" style="background:#2563eb; color:#ffffff; padding:12px 18px; border-radius:8px; text-decoration:none;">
            Auto Gateway
        </a><p>or</p>
        <a href="http://192.168.1.9:8080/bfrn/login" style="background:#2563eb; color:#ffffff; padding:12px 18px; border-radius:8px; text-decoration:none;">
            Password Login
        </a>
    </p>

    {{-- <p style="font-size:12px; color:#6b7280;">
        The approved user should change the temporary password after first default BFRN login.
    </p> --}}
</div>
</body>
</html>
