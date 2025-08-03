<?php

declare(strict_types=1);

namespace Boson\Component\Http\Exception;

class InvalidHeaderNameException extends InvalidHeadersException
{
    public static function becauseHeaderNameIsInvalid(string $name, ?\Throwable $previous = null): self
    {
        $message = \vsprintf('Header name must be compatible with RFC 7230, but "%s" given', [
            \addcslashes($name, '"'),
        ]);

        return new self($message, previous: $previous);
    }

    public static function becauseHeaderNameIsEmpty(?\Throwable $previous = null): self
    {
        return new self('Header name cannot be empty', previous: $previous);
    }

    public static function becauseHeaderNameIsNotString(mixed $name, ?\Throwable $previous = null): self
    {
        $message = \vsprintf('Header name must be non-empty string, but "%s" given', [
            \addcslashes(\get_debug_type($name), '"'),
        ]);

        return new self($message, previous: $previous);
    }

    public static function becauseStringCastingErrorOccurs(\Stringable $name, ?\Throwable $previous = null): self
    {
        $message = 'An error occurred while converting the HTTP header name of type %s to a string';

        return new self(\sprintf($message, $name::class), previous: $previous);
    }
}
