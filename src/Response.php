<?php

declare(strict_types=1);

namespace Boson\Component\Http;

use Boson\Component\Http\Component\Body;
use Boson\Component\Http\Component\MutableHeadersMap;
use Boson\Component\Http\Component\StatusCode;
use Boson\Component\Http\Exception\InvalidBodyException;
use Boson\Component\Http\Exception\InvalidHeadersException;
use Boson\Contracts\Http\Component\Body\BodyProviderInterface;
use Boson\Contracts\Http\Component\Body\EvolvableBodyProviderInterface;
use Boson\Contracts\Http\Component\Body\MutableBodyProviderInterface;
use Boson\Contracts\Http\Component\Headers\EvolvableHeadersProviderInterface;
use Boson\Contracts\Http\Component\Headers\HeadersProviderInterface;
use Boson\Contracts\Http\Component\Headers\MutableHeadersProviderInterface;
use Boson\Contracts\Http\Component\MutableHeadersInterface;
use Boson\Contracts\Http\Component\StatusCode\EvolvableStatusCodeProviderInterface;
use Boson\Contracts\Http\Component\StatusCode\MutableStatusCodeProviderInterface;
use Boson\Contracts\Http\Component\StatusCode\StatusCodeProviderInterface;
use Boson\Contracts\Http\Component\StatusCodeInterface;
use Boson\Contracts\Http\MutableResponseInterface;
use Boson\Contracts\Http\ResponseInterface;

/**
 * @phpstan-import-type InStatusCodeType from EvolvableStatusCodeProviderInterface
 * @phpstan-import-type OutStatusCodeType from StatusCodeProviderInterface
 * @phpstan-import-type OutMutableStatusCodeType from MutableStatusCodeProviderInterface
 * @phpstan-import-type InHeadersType from EvolvableHeadersProviderInterface
 * @phpstan-import-type OutHeadersType from HeadersProviderInterface
 * @phpstan-import-type OutMutableHeadersType from MutableHeadersProviderInterface
 * @phpstan-import-type InBodyType from EvolvableBodyProviderInterface
 * @phpstan-import-type OutBodyType from BodyProviderInterface
 * @phpstan-import-type OutMutableBodyType from MutableBodyProviderInterface
 */
class Response implements MutableResponseInterface
{
    /**
     * @var InBodyType
     */
    final public const \Stringable|string DEFAULT_BODY = '';

    /**
     * @var InStatusCodeType
     */
    final public const StatusCodeInterface|int DEFAULT_STATUS_CODE = StatusCode::Ok;

    /**
     * @var InHeadersType
     */
    final public const iterable DEFAULT_HEADERS = [];

    /**
     * @var OutMutableBodyType
     */
    public string $body {
        /**
         * @return OutMutableBodyType
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
     * @var OutMutableStatusCodeType
     */
    public StatusCodeInterface $status {
        /**
         * @return OutMutableStatusCodeType
         */
        get => $this->status;
        /**
         * @param InStatusCodeType $status
         */
        set(StatusCodeInterface|int $status) => static::castStatusCode($status);
    }

    /**
     * @var OutMutableHeadersType
     */
    public MutableHeadersInterface $headers {
        /**
         * @return OutMutableHeadersType
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
     * @param InBodyType $body
     * @param InStatusCodeType $status
     * @param InHeadersType $headers
     */
    public function __construct(
        \Stringable|string $body = self::DEFAULT_BODY,
        StatusCodeInterface|int $status = self::DEFAULT_STATUS_CODE,
        iterable $headers = self::DEFAULT_HEADERS,
    ) {
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;

        $this->extendHeaders($this->headers);
    }

    /**
     * @param InBodyType $body
     *
     * @return OutMutableBodyType
     * @throws InvalidBodyException
     *
     * @internal This method is not covered by the backward compatibility promise for boson-php/http
     */
    public static function castBody(\Stringable|string $body): string
    {
        return Body::createMutable($body);
    }

    /**
     * @param InStatusCodeType $status
     * @param string|null $reason Reason phrase for new non-standard status-code
     *
     * @return OutMutableStatusCodeType
     *
     * @internal This method is not covered by the backward compatibility promise for boson-php/http
     */
    public static function castStatusCode(StatusCodeInterface|int $status, ?string $reason = null): StatusCodeInterface
    {
        return StatusCode::createMutable($status, $reason);
    }

    /**
     * @param InHeadersType $headers
     *
     * @return OutMutableHeadersType
     * @throws InvalidHeadersException
     *
     * @internal This method is not covered by the backward compatibility promise for boson-php/http
     */
    public static function castHeaders(iterable $headers): MutableHeadersInterface
    {
        return new MutableHeadersMap($headers);
    }

    protected function extendHeaders(MutableHeadersInterface $headers): void
    {
        // Set UTF-8 text/html content header in case of
        // content-type header line is not defined.
        if (!$headers->has('content-type')) {
            $headers->add('content-type', 'text/html; charset=utf-8');
        }

        // Fix unnecessary content-length.
        if ($headers->has('transfer-encoding')) {
            $headers->remove('content-length');
        }
    }

    /**
     * Creates new response instance from another one.
     *
     * @api
     */
    public static function createFromResponse(ResponseInterface $response): self
    {
        if ($response instanceof self) {
            return clone $response;
        }

        return new self(
            body: $response->body,
            status: $response->status,
            headers: $response->headers,
        );
    }

    public function __clone(): void
    {
        /**
         * @link https://wiki.php.net/rfc/readonly_amendment
         */
        $this->headers = clone $this->headers;
    }
}
