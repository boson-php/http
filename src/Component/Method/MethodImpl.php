<?php

declare(strict_types=1);

namespace Boson\Component\Http\Component\Method;

use Boson\Contracts\Http\Component\MethodInterface;

/**
 * @phpstan-require-implements MethodInterface
 */
trait MethodImpl
{
    /**
     * @var non-empty-uppercase-string
     */
    public readonly string $name;

    /**
     * @param non-empty-string $name
     */
    public function __construct(
        string $name,
        public readonly ?bool $isIdempotent = null,
        public readonly ?bool $isSafe = null,
    ) {
        $this->name = \strtoupper($name);
    }

    public function equals(mixed $other): bool
    {
        return $other === $this
            || ($other instanceof MethodInterface
                && $other->name === $this->name);
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
