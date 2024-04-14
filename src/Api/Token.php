<?php

namespace Spinen\Ncentral\Api;

use Carbon\CarbonImmutable;

class Token
{
    // TODO: Is this a good length?
    public const EXPIRE_BUFFER = 5;

    public CarbonImmutable $expires_at;

    public CarbonImmutable $renew_at;

    public function __construct(
        ?array $access = [],
        public ?string $access_token = null,
        protected int $expires_in = 3600,
        ?array $refresh = [],
        public ?string $refresh_token = null,
        public ?int $renew_in = 90000,
        public string $token_type = 'Bearer',
    ) {
        if (! empty($access)) {
            $this->access_token = $access['token'] ?? $access_token;
            $expires_in = $access['expirySeconds'] ?? $expires_in;
            $this->token_type = $access['type'] ?? $token_type;
        }

        if (! empty($refresh)) {
            $this->refresh_token = $access['token'] ?? $refresh_token;
            $renew_in = $access['expirySeconds'] ?? $renew_in;
        }

        $now = CarbonImmutable::now();

        $this->expires_at = $now->addSeconds($expires_in);
        $this->renew_at = $now->addSeconds($renew_in);
    }

    public function __toString(): string
    {
        return $this->token_type.' '.$this->access_token;
    }

    /**
     * If there is a token, has it expired
     */
    public function isExpired(): bool
    {
        return is_null($this->access_token)
            ? false
            : $this->validFor() <= self::EXPIRE_BUFFER;
    }

    /**
     * If there is a token & it has not expired & if provided a scope,
     * check to see if it is allowed scope
     */
    public function isValid(): bool
    {
        return ! is_null($this->access_token) && ! $this->isExpired();
    }

    /**
     * The token expires within the BUFFER
     */
    public function needsRefreshing(): bool
    {
        return is_null($this->refresh_token)
            ? false
            : $this->validFor() <= self::EXPIRE_BUFFER;
    }

    /**
     * If there is a token, how many seconds is left before expires
     */
    public function validFor(): int
    {
        return is_null($this->access_token) || CarbonImmutable::now()->gte($this->expires_at)
            ? 0
            : (int) floor(abs(CarbonImmutable::now()->diffInSeconds($this->expires_at)));
    }
}
