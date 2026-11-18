<?php

namespace App\Services\AntiSpam;

final readonly class CheckResult
{
    public function __construct(
        public bool $allowed,
        public ?string $reasonCode = null,
        public ?string $reasonMessage = null,
        public ?int $retryAfterSeconds = null,
        public array $meta = [],
    ) {}

    public static function allowed(array $meta = []): self
    {
        return new self(true, meta: $meta);
    }

    public static function blocked(
        string $code,
        string $message,
        ?int $retryAfterSeconds = null,
        array $meta = [],
    ): self {
        return new self(false, $code, $message, $retryAfterSeconds, $meta);
    }

    public function isBlocked(): bool
    {
        return ! $this->allowed;
    }
}