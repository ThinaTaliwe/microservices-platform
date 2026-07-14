# BFRN Internal Auth API

## Sprint 3.3 - Initial Integration

### Objective

Remove direct database coupling between the IAM Platform and BFRN.

## Architecture

IAM Platform
    ↓ HTTPS
BFRN Internal Auth API
    ↓
BfrnProvisioningService
    ↓
BFRN Database

## Implemented Endpoint

POST /api/bfrn/internal/auth/user/find

### Purpose

Returns an existing BFRN user by email.

### Validation

Successfully tested inside the `bfrn-php` container.

Result:

- HTTP Status: 200
- Existing user found: true
- User ID: 19

## Rule

The IAM Platform must never access the BFRN database directly.

All communication must occur through the BFRN Internal Auth API.

## Status

✅ Phase 1 Complete

Next step:
- Replace the first `DB::connection('bfrn_mysql')` call in the IAM Platform with an HTTP client.
