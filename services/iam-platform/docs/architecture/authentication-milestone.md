# IAM Platform Authentication Milestone

## Status

Stable and validated in the production-lab environment.

## Implemented authentication flow

1. User submits an email address to IAM Platform.
2. IAM captures device, browser, network, timezone, and request context.
3. IAM evaluates login risk.
4. High-risk requests require supervisor approval.
5. IAM provisions or links the corresponding BFRN user through a signed internal API.
6. Every first authentication session requires a six-digit email OTP.
7. Successful OTP verification creates:
   - an active IAM session;
   - a short-lived one-time BFRN handoff token;
   - an auditable security event.
8. BFRN consumes the handoff token through a signed internal IAM API.
9. BFRN creates its local authenticated session and selects the approved business unit.

## Same-day OTP reuse policy

A user may skip another OTP during the current reauthentication window only when all conditions remain valid:

- the browser presents the correct encrypted HttpOnly IAM session cookie;
- the identity matches;
- the device fingerprint matches;
- current risk remains low;
- the IAM session is active and not revoked;
- inactivity is less than 30 minutes;
- the absolute session lifetime is less than 12 hours;
- the local-day reauthentication cutoff has not passed.

A new device, elevated risk, expired session, revoked session, missing cookie, inactivity timeout, or new calendar day requires OTP again.

## Session policy

- OTP lifetime: 10 minutes.
- OTP maximum attempts: 5.
- OTP replay: prohibited.
- Handoff lifetime: 5 minutes.
- Handoff replay: prohibited.
- IAM session absolute maximum: 12 hours.
- IAM session inactivity limit for reuse: 30 minutes.
- Only one active IAM session is retained per identity and device.
- Creating a replacement session revokes older active sessions for that identity and device.

## Service boundaries

IAM Platform and BFRN no longer read each other's databases directly.

Communication uses internal HTTP APIs protected by timestamped HMAC request signatures:

- IAM to BFRN:
  - find user;
  - list business units;
  - provision user;
  - assign business-unit access.
- BFRN to IAM:
  - consume handoff token;
  - validate IAM session.

Only token hashes, OTP hashes, email hashes, device hashes, and encrypted sensitive payloads are stored.

## Validated controls

The following cases were tested successfully:

- low-risk OTP authentication;
- high-risk supervisor approval followed by OTP;
- existing BFRN user authentication;
- BFRN user provisioning;
- signed handoff consumption;
- invalid OTP rejection;
- OTP lock after five failed attempts;
- expired OTP rejection;
- OTP replay rejection;
- handoff replay rejection;
- IAM session creation;
- same-day session reuse without another OTP;
- revoked session rejection during a subsequent IAM login;
- replacement of older same-device IAM sessions.

## Deferred before production

Continuous termination of an already-open BFRN Laravel session after IAM revocation is deferred.

The inactive supporting components remain available:

- IAM session-validation endpoint;
- BFRN IAM validation client;
- BFRN `EnsureValidIamSession` middleware.

The middleware is intentionally not attached to active BFRN routes.

Current implication: IAM revocation blocks future IAM session reuse, but an already-open BFRN session may remain valid until BFRN logout or local session expiry.

This item must be completed or explicitly risk-accepted before production release.

## Operational notes

- Production must use HTTPS so the IAM session cookie is marked `Secure`.
- Production secrets must be externally supplied through environment variables.
- Development placeholder credentials must never be reused in production.
- BFRN currently uses file-backed Laravel sessions. Redis or database-backed sessions are required before horizontally scaling BFRN.
