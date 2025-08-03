<?php

declare(strict_types=1);

namespace Boson\Component\Http;

use Boson\Component\Http\Component\Body;
use Boson\Component\Http\Component\HeadersMap;
use Boson\Component\Http\Component\Method;
use Boson\Component\Http\Exception\InvalidBodyException;
use Boson\Component\Http\Exception\InvalidHeadersException;
use Boson\Component\Uri\Factory\UriFactory;
use Boson\Contracts\Http\Component\Body\BodyProviderInterface;
use Boson\Contracts\Http\Component\Body\EvolvableBodyProviderInterface;
use Boson\Contracts\Http\Component\Headers\EvolvableHeadersProviderInterface;
use Boson\Contracts\Http\Component\Headers\HeadersProviderInterface;
use Boson\Contracts\Http\Component\HeadersInterface;
use Boson\Contracts\Http\Component\Method\EvolvableMethodProviderInterface;
use Boson\Contracts\Http\Component\Method\MethodProviderInterface;
use Boson\Contracts\Http\Component\MethodInterface;
use Boson\Contracts\Http\Component\Url\EvolvableUrlProviderInterface;
use Boson\Contracts\Http\Component\Url\UrlProviderInterface;
use Boson\Contracts\Http\EvolvableRequestInterface;
use Boson\Contracts\Http\RequestInterface;
use Boson\Contracts\Uri\Factory\UriFactoryInterface;
use Boson\Contracts\Uri\UriInterface;

/**
 * @phpstan-import-type InMethodType from EvolvableMethodProviderInterface
 * @phpstan-import-type OutMethodType from MethodProviderInterface
 * @phpstan-import-type InUrlType from EvolvableUrlProviderInterface
 * @phpstan-import-type OutUrlType from UrlProviderInterface
 * @phpstan-import-type InHeadersType from EvolvableHeadersProviderInterface
 * @phpstan-import-type OutHeadersType from HeadersProviderInterface
 * @phpstan-import-type InBodyType from EvolvableBodyProviderInterface
 * @phpstan-import-type OutBodyType from BodyProviderInterface
 */
class Request implements EvolvableRequestInterface
{
    /**
     * @var InMethodType
     */
    final public const \Stringable|string DEFAULT_METHOD = Method::Get;

    /**
     * @var InUrlType
     */
    final public const \Stringable|string DEFAULT_URL = 'about:blank';

    /**
     * @var InHeadersType
     */
    final public const iterable DEFAULT_HEADERS = [];

    /**
     * @var InBodyType
     */
    final public const \Stringable|string DEFAULT_BODY = '';

    /**
     * @var OutMethodType
     */
    public protected(set) MethodInterface $method {
        /**
         * @return OutMethodType
         */
        get => $this->method;
        /**
         * @param InMethodType $method
         */
        set(\Stringable|string $method) => static::castMethod($method);
    }

    /**
     * @var OutUrlType
     */
    public protected(set) UriInterface $url {
        /**
         * @return OutUrlType
         */
        get => $this->url;
        /**
         * @param InUrlType $url
         */
        set(\Stringable|string $url) => static::castUrl($url);
    }

    private static UriFactoryInterface $uriFactory;

    /**
     * @var OutHeadersType
     */
    public protected(set) HeadersInterface $headers {
        /**
         * @return OutHeadersType
         */
        get => $this->headers;
        /**
         * @param InHeadersType $headers
         *
         * @throws InvalidHeadersException
         */
        set(iterable $headers) => static::castHeaders($headers);
    }

    /**
     * @var OutBodyType
     */
    public protected(set) string $body {
        /**
         * @return OutBodyType
         */
        get => $this->body;
        /**
         * @param InBodyType $body
         *
         * @throws InvalidBodyException
         */
        set(\Stringable|string $body) => static::castBody($body);
    }

    /**
     * @param InMethodType $method
     * @param InUrlType $url
     * @param InHeadersType $headers
     * @param InBodyType $body
     */
    public function __construct(
        \Stringable|string $method = self::DEFAULT_METHOD,
        \Stringable|string $url = self::DEFAULT_URL,
        iterable $headers = self::DEFAULT_HEADERS,
        \Stringable|string $body = self::DEFAULT_BODY,
    ) {
        $this->method = $method;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * @param InMethodType $method
     *
     * @return OutMethodType
     */
    public static function castMethod(\Stringable|string $method): MethodInterface
    {
        return Method::create($method);
    }

    /**
     * @param InUrlType $url
     *
     * @return OutUrlType
     */
    public static function castUrl(\Stringable|string $url): UriInterface
    {
        $factory = self::$uriFactory ??= new UriFactory();

        return $factory->createUriFromString($url);
    }

    /**
     * @param InHeadersType $headers
     *
     * @return OutHeadersType
     * @throws InvalidHeadersException
     */
    public static function castHeaders(iterable $headers): HeadersInterface
    {
        return new HeadersMap($headers);
    }

    /**
     * @param InBodyType $body
     *
     * @return OutBodyType
     * @throws InvalidBodyException
     */
    public static function castBody(\Stringable|string $body): string
    {
        return Body::create($body);
    }

    /**
     * Creates new request instance from another one.
     *
     * @api
     */
    public static function createFromRequest(RequestInterface $request): self
    {
        if ($request instanceof self) {
            return clone $request;
        }

        return new self(
            method: $request->method,
            url: $request->url,
            headers: $request->headers,
            body: $request->body,
        );
    }

    public function withMethod(MethodInterface|\Stringable|string $method): self
    {
        $self = clone $this;
        $self->method = $method;

        return $self;
    }

    public function withUrl(UriInterface|\Stringable|string $url): self
    {
        $self = clone $this;
        $self->url = $url;

        return $self;
    }

    public function withHeaders(iterable $headers): self
    {
        $self = clone $this;
        $self->headers = $headers;

        return $self;
    }

    public function withBody(\Stringable|string $body): self
    {
        $self = clone $this;
        $self->body = $body;

        return $self;
    }

    public function __clone(): void
    {
        /**
         * @link https://wiki.php.net/rfc/readonly_amendment
         */
        $this->headers = clone $this->headers;
    }
}
