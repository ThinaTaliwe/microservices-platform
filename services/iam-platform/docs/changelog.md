
## 2026-07-14 — IAM authentication milestone

- Added signed IAM/BFRN internal API communication.
- Removed active direct cross-service database dependencies.
- Added supervisor approval and BFRN user provisioning.
- Added mandatory email OTP authentication.
- Added OTP expiry, attempt locking, and replay protection.
- Added one-time BFRN handoff tokens.
- Added device-bound IAM sessions and same-day OTP reuse.
- Added same-device session replacement and revocation.
- Deferred continuous forced logout of active BFRN sessions until pre-production hardening.
