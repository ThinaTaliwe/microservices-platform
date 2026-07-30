<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>IAM Verification API Test</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            color: #111827;
            font-family: Arial, sans-serif;
        }

        main {
            width: min(960px, calc(100% - 32px));
            margin: 32px auto;
        }

        .card {
            margin-bottom: 20px;
            padding: 24px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            background: #ffffff;
        }

        h1,
        h2 {
            margin-top: 0;
        }

        h1 {
            margin-bottom: 8px;
            font-size: 26px;
        }

        h2 {
            font-size: 18px;
        }

        p {
            color: #4b5563;
            line-height: 1.5;
        }

        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 600;
        }

        input,
        select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #9ca3af;
            border-radius: 7px;
            background: #ffffff;
            font-size: 14px;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            padding: 10px 15px;
            border: 0;
            border-radius: 7px;
            background: #1f2937;
            color: #ffffff;
            cursor: pointer;
            font-weight: 600;
        }

        button.secondary {
            background: #4b5563;
        }

        button.danger {
            background: #991b1b;
        }

        button:disabled {
            cursor: wait;
            opacity: 0.55;
        }

        .endpoint {
            margin: 8px 0;
            padding: 10px 12px;
            overflow-wrap: anywhere;
            border-radius: 6px;
            background: #f3f4f6;
            font-family: monospace;
            font-size: 13px;
        }

        .method {
            display: inline-block;
            min-width: 62px;
            font-weight: 700;
        }

        .state {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(190px, 1fr)
            );
            gap: 12px;
        }

        .state div {
            padding: 12px;
            border-radius: 7px;
            background: #f3f4f6;
        }

        .state strong {
            display: block;
            margin-bottom: 4px;
        }

        pre {
            min-height: 180px;
            margin: 0;
            padding: 16px;
            overflow: auto;
            border-radius: 8px;
            background: #111827;
            color: #e5e7eb;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
<main>
    <section class="card">
        <h1>IAM Verification API Test</h1>

        <p>
            Generate an emailed OTP, verify it, and test the
            complete verification challenge lifecycle.
        </p>

        <label for="baseUrl">API base URL</label>
        <input id="baseUrl" type="text">

        <label for="email">Bootstrap administrator</label>
        <select id="email">
            <option value="thina.taliwe2@gmail.com">
                thina.taliwe2@gmail.com
            </option>

            <option value="korrie@bchem.co.za">
                korrie@bchem.co.za
            </option>
        </select>

        <label for="otp">Emailed six-digit OTP</label>
        <input
            id="otp"
            type="text"
            inputmode="numeric"
            maxlength="6"
            placeholder="Enter OTP after receiving the email"
        >
    </section>

    <section class="card">
        <h2>API URLs</h2>

        <div class="endpoint">
            <span class="method">POST</span>
            <span id="createUrl"></span>
        </div>

        <div class="endpoint">
            <span class="method">GET</span>
            <span id="statusUrl"></span>
        </div>

        <div class="endpoint">
            <span class="method">POST</span>
            <span id="verifyUrl"></span>
        </div>

        <div class="endpoint">
            <span class="method">POST</span>
            <span id="resendUrl"></span>
        </div>

        <div class="endpoint">
            <span class="method">DELETE</span>
            <span id="revokeUrl"></span>
        </div>
    </section>

    <section class="card">
        <h2>Current test state</h2>

        <div class="state">
            <div>
                <strong>Challenge</strong>
                <span id="challengeState">Not generated</span>
            </div>

            <div>
                <strong>API token</strong>
                <span id="tokenState">Not generated</span>
            </div>
        </div>

        <div class="buttons">
            <button onclick="generateOtp()">
                Generate OTP
            </button>

            <button
                class="secondary"
                onclick="checkStatus()"
            >
                Check Status
            </button>

            <button onclick="verifyOtp()">
                Verify OTP
            </button>

            <button onclick="verifyApiToken()">
                Verify API Token
            </button>

            <button
                class="secondary"
                onclick="resendOtp()"
            >
                Resend OTP
            </button>

            <button
                class="danger"
                onclick="revokeChallenge()"
            >
                Revoke Challenge
            </button>
        </div>
    </section>

    <section class="card">
        <h2>API result</h2>
        <pre id="result">Ready.</pre>
    </section>
</main>

<script src="/js/api-test-verification.js"></script>
</body>
</html>
