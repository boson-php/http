<?php

declare(strict_types=1);

namespace Boson\Component\Http\Component;

use Boson\Component\Http\Component\Headers\Header;
use Boson\Component\Http\Exception\InvalidHeaderValueException;
use Boson\Contracts\Http\Component\EvolvableHeadersInterface;
use Boson\Contracts\Http\Component\HeadersInterface;

/**
 * An implementation of immutable headers list.
 *
 * @phpstan-import-type InHeaderNameType from HeadersInterface
 * @phpstan-import-type OutHeaderNameType from HeadersInterface
 * @phpstan-import-type InHeaderValueType from HeadersInterface
 * @phpstan-import-type OutHeaderValueType from HeadersInterface
 * @phpstan-import-type InHeaderValuesType from HeadersInterface
 * @phpstan-import-type OutHeaderValuesType from HeadersInterface
 * @phpstan-import-type InHeadersListType from HeadersInterface
 * @phpstan-import-type OutHeadersListType from HeadersInterface
 *
 * @template-implements \IteratorAggregate<OutHeaderNameType, OutHeaderValueType>
 */
class HeadersMap implements EvolvableHeadersInterface, \IteratorAggregate
{
    /**
     * @var OutHeadersListType
     */
    protected array $lines;

    /**
     * Expects list of header values in format:
     *
     * ```
     * [
     *      'lowercase-header-name' => ['value-1', 'value-2'],
     *      'lowercase-header-name-2' => ['value-1'],
     * ]
     * ```
     *
     * @param InHeadersListType $headers
     */
    final public function __construct(iterable $headers = [])
    {
        $this->lines = static::castHeadersList($headers);
    }

    /**
     * @param InHeaderValuesType $values
     *
     * @return OutHeaderValuesType
     */
    public static function castHeaderValues(string|\Stringable|iterable $values, bool $validate = true): array
    {
        $result = [];

        if (!\is_iterable($values)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            $result[] = Header::castHeaderValue(match (true) {
                \is_string($value), $value instanceof \Stringable => $value,
                default => throw InvalidHeaderValueException::becauseHeaderValueIsNotString($value),
            }, $validate);
        }

        return $result;
    }

    /**
     * @param InHeadersListType $headers
     *
     * @return OutHeadersListType
     */
    public static function castHeadersList(iterable $headers, bool $validate = true): array
    {
        $result = [];

        foreach ($headers as $name => $values) {
            $normalizedName = Header::castHeaderName($name, $validate);
            $normalizedValues = static::castHeaderValues($values, $validate);

            $result[$normalizedName] = isset($result[$normalizedName])
                ? \array_merge($result[$normalizedName], $normalizedValues)
                : $normalizedValues;
        }

        return $result;
    }

    /**
     * Creates new headers list instance from another one.
     *
     * @api
     */
    public static function createFromHeaders(HeadersInterface $headers): self
    {
        if ($headers instanceof self) {
            return clone $headers;
        }

        return new self($headers->toArray());
    }

    public function withAddedHeader(string|\Stringable $name, string|\Stringable $value): self
    {
        if ($name === '') {
            return $this;
        }

        $self = clone $this;
        $self->add($name, $value);

        return $self;
    }

    public function withHeader(\Stringable|string $name, iterable|\Stringable|string $values): self
    {
        $self = clone $this;
        $self->set($name, $values);

        return $self;
    }

    public function withoutHeader(string|\Stringable $name): self
    {
        if ($name === '') {
            return $this;
        }

        $self = clone $this;
        $self->remove($name);

        return $self;
    }

    /**
     * @param InHeaderNameType $name
     * @param InHeaderValuesType $values
     */
    protected function set(\Stringable|string $name, iterable|\Stringable|string $values): void
    {
        $this->lines[Header::castHeaderName($name)] = self::castHeaderValues($values);
    }

    /**
     * @param InHeaderNameType $name
     * @param InHeaderValueType $value
     */
    protected function add(\Stringable|string $name, \Stringable|string $value): void
    {
        $this->lines[Header::castHeaderName($name)][] = Header::castHeaderValue($value);
    }

    /**
     * @param InHeaderNameType $name
     */
    protected function remove(\Stringable|string $name): void
    {
        if ($name === '') {
            return;
        }

        unset($this->lines[Header::castHeaderName($name, false)]);
    }

    protected function removeAll(): void
    {
        $this->lines = [];
    }

    public function first(string|\Stringable $name, string|\Stringable|null $default = null): ?string
    {
        $normalizedName = Header::castHeaderName($name, false);
        $lines = $this->lines;

        if (\array_key_exists($normalizedName, $lines)) {
            $first = $lines[$normalizedName][0] ?? null;

            if ($first !== null) {
                return $first;
            }
        }

        if ($default === null) {
            return null;
        }

        return Header::castHeaderValue($default, false);
    }

    public function all(string|\Stringable $name): array
    {
        return $this->lines[Header::castHeaderName($name, false)]
            ?? [];
    }

    public function has(string|\Stringable $name): bool
    {
        return \array_key_exists(Header::castHeaderName($name, false), $this->lines);
    }

    public function contains(string|\Stringable $name, string|\Stringable $value): bool
    {
        $normalizedName = Header::castHeaderName($name, false);
        $normalizedValue = Header::castHeaderValue($value, false);

        return \in_array($normalizedValue, $this->lines[$normalizedName] ?? [], true);
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->lines as $index => $values) {
            $name = (string) $index;

            foreach ($values as $value) {
                yield $name => $value;
            }
        }
    }

    public function toArray(): array
    {
        return $this->lines;
    }

    public function count(): int
    {
        return \count($this->lines);
    }
}
