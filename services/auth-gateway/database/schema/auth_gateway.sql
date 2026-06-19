CREATE DATABASE IF NOT EXISTS auth_gateway
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE auth_gateway;

CREATE TABLE IF NOT EXISTS auth_identities (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    bfrn_user_id BIGINT UNSIGNED NULL,
    email_hash CHAR(64) NOT NULL,
    email_encrypted TEXT NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    last_seen_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_auth_identities_email_hash (email_hash),
    KEY idx_auth_identities_bfrn_user_id (bfrn_user_id),
    KEY idx_auth_identities_status (status)
);

CREATE TABLE IF NOT EXISTS auth_devices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    auth_identity_id BIGINT UNSIGNED NOT NULL,
    device_hash CHAR(64) NOT NULL,
    device_payload_encrypted TEXT NOT NULL,
    trusted TINYINT NOT NULL DEFAULT 0,
    first_seen_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_auth_devices_identity_device (auth_identity_id, device_hash),
    KEY idx_auth_devices_hash (device_hash),
    CONSTRAINT fk_auth_devices_identity
        FOREIGN KEY (auth_identity_id) REFERENCES auth_identities(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS auth_login_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    auth_identity_id BIGINT UNSIGNED NULL,
    email_hash CHAR(64) NOT NULL,
    ip_hash CHAR(64) NULL,
    device_hash CHAR(64) NULL,
    payload_encrypted LONGTEXT NOT NULL,
    risk_level VARCHAR(30) NOT NULL DEFAULT 'low',
    decision VARCHAR(30) NOT NULL DEFAULT 'pending',
    decision_reason VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_auth_login_attempts_identity (auth_identity_id),
    KEY idx_auth_login_attempts_email_hash (email_hash),
    KEY idx_auth_login_attempts_decision (decision),
    KEY idx_auth_login_attempts_risk_level (risk_level),
    CONSTRAINT fk_auth_login_attempts_identity
        FOREIGN KEY (auth_identity_id) REFERENCES auth_identities(id)
        ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS auth_pending_approvals (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    login_attempt_id BIGINT UNSIGNED NOT NULL,
    auth_identity_id BIGINT UNSIGNED NULL,
    supervisor_email_hash CHAR(64) NOT NULL,
    approval_token_hash CHAR(64) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    expires_at TIMESTAMP NOT NULL,
    decided_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_auth_pending_approvals_token (approval_token_hash),
    KEY idx_auth_pending_approvals_status (status),
    CONSTRAINT fk_auth_pending_approvals_attempt
        FOREIGN KEY (login_attempt_id) REFERENCES auth_login_attempts(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS auth_handoff_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    auth_identity_id BIGINT UNSIGNED NOT NULL,
    bfrn_user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_auth_handoff_tokens_token (token_hash),
    KEY idx_auth_handoff_tokens_identity (auth_identity_id),
    CONSTRAINT fk_auth_handoff_tokens_identity
        FOREIGN KEY (auth_identity_id) REFERENCES auth_identities(id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS auth_security_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    auth_identity_id BIGINT UNSIGNED NULL,
    login_attempt_id BIGINT UNSIGNED NULL,
    event VARCHAR(80) NOT NULL,
    payload_encrypted LONGTEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_auth_security_events_identity (auth_identity_id),
    KEY idx_auth_security_events_attempt (login_attempt_id),
    KEY idx_auth_security_events_event (event)
);
