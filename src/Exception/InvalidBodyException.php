<?php

declare(strict_types=1);

namespace Boson\Component\Http\Exception;

class InvalidBodyException extends InvalidComponentArgumentException
{
    public static function becauseStringCastingErrorOccurs(\Stringable $body, \Throwable $e): self
    {
        $message = 'An error occurred while converting the HTTP body of type %s to a string';

        return new self(\sprintf($message, $body::class), previous: $e);
    }
}
