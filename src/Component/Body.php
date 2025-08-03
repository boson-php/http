<?php

declare(strict_types=1);

namespace Boson\Component\Http\Component;

use Boson\Component\Http\Exception\InvalidBodyException;
use Boson\Contracts\Http\Component\Body\BodyProviderInterface;
use Boson\Contracts\Http\Component\Body\EvolvableBodyProviderInterface;
use Boson\Contracts\Http\Component\Body\MutableBodyProviderInterface;

/**
 * Representation of HTTP body.
 *
 * Currently, it only implements methods for type-casting a specific HTTP body
 * and does not allow creating instances; this behavior may be
 * added in the future.
 *
 * @phpstan-import-type InBodyType from EvolvableBodyProviderInterface
 * @phpstan-import-type OutBodyType from BodyProviderInterface
 * @phpstan-import-type OutMutableBodyType from MutableBodyProviderInterface
 */
final readonly class Body
{
    private function __construct() {}

    /**
     * Creates a new immutable HTTP body from user-defined body value.
     *
     * @param InBodyType $body User-defined HTTP body value
     *
     * @return OutBodyType Returned formatted (and validated) HTTP body
     * @throws InvalidBodyException in case of body creation error occurs
     *
     * @phpstan-ignore throws.unusedType
     */
    public static function create(\Stringable|string $body): string
    {
        if ($body instanceof \Stringable) {
            try {
                $scalar = (string) $body;
                /** @phpstan-ignore-next-line : PHPStan false-positive, this is not "dead catch" */
            } catch (\Throwable $e) {
                throw InvalidBodyException::becauseStringCastingErrorOccurs($body, $e);
            }

            $body = $scalar;
        }

        return $body;
    }

    /**
     * Creates a new mutable HTTP body from user-defined body value.
     *
     * @param InBodyType $body User-defined HTTP body value
     *
     * @return OutMutableBodyType Returned formatted (and validated) HTTP body
     * @throws InvalidBodyException in case of body creation error occurs
     */
    public static function createMutable(\Stringable|string $body): string
    {
        // Mutable HTTP body is similar to immutable
        return self::create($body);
    }
}
