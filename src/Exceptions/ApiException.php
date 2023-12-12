<?php

namespace Spinen\Ncentral\Exceptions;

use RuntimeException;
use Throwable;

class ApiException extends RuntimeException
{
    protected string $body;

    protected int $status;

    public function __construct(
        string $message,
        int $code,
        ?Throwable $previous = null,
        string $body = null,
    ) {
        parent::__construct(message: $message, code: $code, previous: $previous);

        $this->body = $body;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
