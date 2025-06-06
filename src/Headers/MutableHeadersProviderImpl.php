<?php

declare(strict_types=1);

namespace Boson\Component\Http\Headers;

use Boson\Component\Http\MutableHeadersMap;
use Boson\Contracts\Http\Headers\MutableHeadersProviderInterface;
use Boson\Contracts\Http\MutableHeadersInterface;

/**
 * @api
 *
 * @phpstan-require-implements MutableHeadersProviderInterface
 *
 * @phpstan-import-type HeadersListInputType from MutableHeadersProviderInterface
 * @phpstan-import-type MutableHeadersListOutputType from MutableHeadersProviderInterface
 *
 * @phpstan-ignore trait.unused
 */
trait MutableHeadersProviderImpl
{
    /**
     * @var MutableHeadersListOutputType
     */
    public MutableHeadersInterface $headers {
        get => $this->headers;
        /**
         * @param HeadersListInputType $headers
         */
        set(iterable $headers) => self::castMutableHeaders($headers);
    }

    /**
     * @param HeadersListInputType $headers
     *
     * @return MutableHeadersListOutputType
     */
    public static function castMutableHeaders(iterable $headers): MutableHeadersInterface
    {
        return MutableHeadersMap::createFromIterable($headers);
    }
}
