<?php

declare(strict_types=1);

namespace Boson\Component\Http\Component\Headers;

use Boson\Component\Http\Exception\InvalidHeaderNameException;
use Boson\Component\Http\Exception\InvalidHeaderValueException;
use Boson\Contracts\Http\Component\HeadersInterface;

/**
 * Representation of a single header line: Header name and value pair.
 *
 * Currently, it only implements methods for type-casting and validating
 * a specific header line and does not allow creating instances; this
 * behavior may be added in the future.
 *
 * @phpstan-import-type InHeaderNameType from HeadersInterface
 * @phpstan-import-type OutHeaderNameType from HeadersInterface
 * @phpstan-import-type InHeaderValueType from HeadersInterface
 * @phpstan-import-type OutHeaderValueType from HeadersInterface
 * @phpstan-import-type InHeaderValuesType from HeadersInterface
 * @phpstan-import-type OutHeaderValuesType from HeadersInterface
 * @phpstan-import-type InHeadersListType from HeadersInterface
 * @phpstan-import-type OutHeadersListType from HeadersInterface
 */
final readonly class Header
{
    /**
     * PCRE pattern to validate HTTP header name.
     *
     * @var non-empty-string
     */
    private const string PATTERN_RFC7230_HEADER_NAME = '/^[!#$%&\'*+.^_`|~0-9A-Za-z-]+$/D';

    /**
     * PCRE pattern to validate HTTP header value.
     *
     * @var non-empty-string
     */
    private const string PATTERN_RFC7230_HEADER_VALUE = '/^[ \t\x21-\x7E\x80-\xFF]*$/D';

    private function __construct() {}

    /**
     * Cast a header name to valid HTTP header name string.
     *
     * You may pass additional argument "$validate" to enable or disable name
     * validation according to RFC 7230.
     *
     * Validation MUST be enabled when storing a name within a header list
     * and for further use. Validation MAY be disabled if the name is used
     * only to retrieve data from a header list.
     *
     * @param InHeaderNameType|int $name Expected user-defined header name to
     *        cast.
     *
     *        Note that the name can be an {@see int}. This is due to a
     *        bug/feature in PHP that automatically converts string keys
     *        to an integers.
     *
     *        ```
     *        $headers = ['0' => [ 'value' ]];
     *
     *        var_dump($headers);
     *
     *        // array:1 [
     *        //   0 => array:1 [ 0 => "value" ]
     *        // ]
     *        ```
     *
     * @return OutHeaderNameType Returned formatted (and validated) header name
     * @throws InvalidHeaderNameException in case of passed user-defined header
     *         name is invalid or contain invalid characters
     */
    public static function castHeaderName(\Stringable|string|int $name, bool $validate = true): string
    {
        if ($name instanceof \Stringable || \is_int($name)) {
            try {
                $scalar = (string) $name;
                /** @phpstan-ignore-next-line : PHPStan false-positive, this is not "dead catch" */
            } catch (\Throwable $e) {
                throw InvalidHeaderNameException::becauseStringCastingErrorOccurs($name, $e);
            }

            $name = $scalar;
        }

        if ($name === '') {
            throw InvalidHeaderNameException::becauseHeaderNameIsEmpty();
        }

        if ($validate) {
            self::validateHeaderNameOrFail($name);
        }

        /** @var non-empty-lowercase-string */
        return \strtolower($name);
    }

    /**
     * Validates a header name for compliance with the RFC 7230
     *
     * @link http://tools.ietf.org/html/rfc7230#section-3.2
     *
     * @throws InvalidHeaderNameException in case of passed header name
     *         contain invalid characters
     */
    private static function validateHeaderNameOrFail(string $name): void
    {
        if (\preg_match(self::PATTERN_RFC7230_HEADER_NAME, $name) === 1) {
            return;
        }

        throw InvalidHeaderNameException::becauseHeaderNameIsInvalid($name);
    }

    /**
     * Cast a header value to valid HTTP header value string.
     *
     * You may pass additional argument "$validate" to enable or disable value
     * validation according to RFC 7230.
     *
     * Validation MUST be enabled when storing a value within a header list
     * and for further use. Validation MAY be disabled if the value is used
     * only to retrieve data from a header list (For example, when specified
     * as a default value in {@see HeadersInterface::first()} method).
     *
     * @param InHeaderValueType $value Expected user-defined header value to cast
     *
     * @return OutHeaderValueType Returned formatted (and validated) header value
     * @throws InvalidHeaderValueException in case of passed user-defined header
     *         value is not valid or contain invalid characters
     */
    public static function castHeaderValue(\Stringable|string $value, bool $validate = true): string
    {
        if ($value instanceof \Stringable) {
            try {
                $scalar = (string) $value;
                /** @phpstan-ignore-next-line : PHPStan false-positive, this is not "dead catch" */
            } catch (\Throwable $e) {
                throw InvalidHeaderValueException::becauseStringCastingErrorOccurs($value, $e);
            }

            $value = $scalar;
        }

        if ($validate) {
            self::validateHeaderValueOrFail($value);
        }

        //
        // @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2.4
        //
        // Whitespaces and tabs are valid header value chars but must
        // be removed in case of value is prefixed or suffixed:
        //
        // - `obs-fold = CRLF 1*( SP / HTAB )`
        //
        // TBD: LF/CRLF should be also removed?
        //
        return \trim($value, " \t");
    }

    /**
     * Validates a header value for compliance with the RFC 7230
     *
     * @link http://tools.ietf.org/html/rfc7230#section-3.2
     *
     * @throws InvalidHeaderValueException in case of passed user-defined header
     *         value contain invalid characters
     */
    public static function validateHeaderValueOrFail(string $value): void
    {
        if (\preg_match(self::PATTERN_RFC7230_HEADER_VALUE, $value) === 1) {
            return;
        }

        throw InvalidHeaderValueException::becauseHeaderValueIsInvalid($value);
    }
}
