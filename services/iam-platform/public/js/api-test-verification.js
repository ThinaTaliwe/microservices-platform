const state = {
    challengeId: '',
    apiTokenAvailable: false,
};

const result = document.getElementById('result');
const baseInput = document.getElementById('baseUrl');
const csrf = document
    .querySelector('meta[name="csrf-token"]')
    .content;

baseInput.value = window.location.origin;
baseInput.readOnly = true;

function internalChallengePath() {
    return '/api/internal/verification/challenges/'
        + (state.challengeId || '{challengeUuid}');
}

function updateUi() {
    const base = window.location.origin;
    const challenge = internalChallengePath();

    document.getElementById('createUrl').textContent =
        base + '/api/internal/verification/challenges';

    document.getElementById('statusUrl').textContent =
        base + challenge;

    document.getElementById('verifyUrl').textContent =
        base + challenge + '/verify';

    document.getElementById('resendUrl').textContent =
        base + challenge + '/resend';

    document.getElementById('revokeUrl').textContent =
        base + challenge;

    document.getElementById('challengeState').textContent =
        state.challengeId || 'Not generated';

    document.getElementById('tokenState').textContent =
        state.apiTokenAvailable
            ? 'Available securely on server'
            : 'Not available';
}

async function apiTest(method, path, payload = null) {
    result.textContent = 'Requesting...';

    const options = {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
    };

    if (payload !== null) {
        options.body = JSON.stringify(payload);
    }

    const response = await fetch(path, options);
    const text = await response.text();

    let data;

    try {
        data = JSON.parse(text);
    } catch {
        data = {
            http_status: response.status,
            raw_response: text,
        };
    }

    const challengeId =
        data?.response?.data?.challenge_id;

    if (challengeId) {
        state.challengeId = challengeId;
    }

    state.apiTokenAvailable =
        data?.test_state?.api_token_available === true;

    updateUi();

    result.textContent = JSON.stringify(data, null, 2);

    return data;
}

async function run(action) {
    try {
        await action();
    } catch (error) {
        result.textContent = JSON.stringify({
            error: error.message,
        }, null, 2);
    }
}

function requireChallenge() {
    if (!state.challengeId) {
        throw new Error('Click Generate OTP first.');
    }
}

function generateOtp() {
    run(async () => {
        state.challengeId = '';
        state.apiTokenAvailable = false;

        document.getElementById('otp').value = '';

        updateUi();

        await apiTest(
            'POST',
            '/api-test/verification/generate',
            {
                email: document.getElementById('email').value,
            }
        );
    });
}

function checkStatus() {
    run(async () => {
        requireChallenge();

        await apiTest(
            'GET',
            '/api-test/verification/'
                + state.challengeId
        );
    });
}

function verifyOtp() {
    run(async () => {
        requireChallenge();

        const code = document
            .getElementById('otp')
            .value
            .trim();

        if (!/^\d{6}$/.test(code)) {
            throw new Error(
                'Enter the emailed six-digit OTP.'
            );
        }

        await apiTest(
            'POST',
            '/api-test/verification/'
                + state.challengeId
                + '/verify-otp',
            {code}
        );
    });
}

function verifyApiToken() {
    run(async () => {
        requireChallenge();

        if (!state.apiTokenAvailable) {
            throw new Error(
                'Generate a fresh challenge first.'
            );
        }

        await apiTest(
            'POST',
            '/api-test/verification/'
                + state.challengeId
                + '/verify-token'
        );
    });
}

function resendOtp() {
    run(async () => {
        requireChallenge();

        await apiTest(
            'POST',
            '/api-test/verification/'
                + state.challengeId
                + '/resend'
        );
    });
}

function revokeChallenge() {
    run(async () => {
        requireChallenge();

        await apiTest(
            'DELETE',
            '/api-test/verification/'
                + state.challengeId
        );
    });
}

updateUi();
