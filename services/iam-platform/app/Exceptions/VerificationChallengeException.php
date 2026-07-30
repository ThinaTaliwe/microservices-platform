<?php

namespace App\Exceptions;

use RuntimeException;

final class VerificationChallengeException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $httpStatus
    ) {
        parent::__construct($message);
    }
}
