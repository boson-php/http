<?php

declare(strict_types=1);

namespace Boson\Component\Http\Exception;

class InvalidHeaderValueException extends InvalidHeadersException
{
    public static function becauseHeaderValueIsInvalid(string $name, ?\Throwable $previous = null): self
    {
        $message = \vsprintf('Header value must be compatible with RFC 7230, but "%s" given', [
            \addcslashes($name, '"'),
        ]);

        return new self($message, previous: $previous);
    }

    public static function becauseHeaderValueIsNotString(mixed $value, ?\Throwable $previous = null): self
    {
        $message = \vsprintf('Header value must be a string, but "%s" given', [
            \addcslashes(\get_debug_type($value), '"'),
        ]);

        return new self($message, previous: $previous);
    }

    public static function becauseStringCastingErrorOccurs(\Stringable $value, \Throwable $e): self
    {
        $message = 'An error occurred while converting the HTTP header of type %s to a string';

        return new self(\sprintf($message, $value::class), previous: $e);
    }
}
