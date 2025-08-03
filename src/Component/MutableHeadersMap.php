<?php

declare(strict_types=1);

namespace Boson\Component\Http\Component;

use Boson\Contracts\Http\Component\MutableHeadersInterface;

class MutableHeadersMap extends HeadersMap implements MutableHeadersInterface
{
    public function set(\Stringable|string $name, iterable|\Stringable|string $values): void
    {
        parent::set($name, $values);
    }

    public function add(\Stringable|string $name, \Stringable|string $value): void
    {
        parent::add($name, $value);
    }

    public function remove(\Stringable|string $name): void
    {
        parent::remove($name);
    }

    public function removeAll(): void
    {
        parent::removeAll();
    }
}
