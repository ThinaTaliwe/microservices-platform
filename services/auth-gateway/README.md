# Auth Gateway

Dedicated Laravel authentication gateway for BFRN.

## Purpose

- Email-only login entry
- Risk checks
- Device and network capture
- Supervisor approval
- One-time BFRN login handoff
- Encrypted security event storage

## Architecture Decision

BFRN remains the source of truth for:

- users
- systems
- companies
- business units
- components
- permissions

Auth Gateway owns:

- login attempts
- devices
- approval workflow
- handoff tokens
- security events
