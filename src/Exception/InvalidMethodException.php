<?php

declare(strict_types=1);

namespace Boson\Component\Http\Exception;

class InvalidMethodException extends InvalidComponentArgumentException
{
    public static function becauseMethodIsEmpty(?\Throwable $previous = null): self
    {
        return new self('HTTP method name cannot be empty', previous: $previous);
    }

    public static function becauseStringCastingErrorOccurs(\Stringable $method, \Throwable $e): self
    {
        $message = 'An error occurred while converting the HTTP method of type %s to a string';

        return new self(\sprintf($message, $method::class), previous: $e);
    }
}
