<?php

declare(strict_types=1);

/*
 * This file is part of the CleverAge/SoapProcessBundle package.
 *
 * Copyright (c) Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\SoapProcessBundle\Logger;

/**
 * Masks the credentials of the SOAP traces and options before logging them: the logs can be stored and displayed
 * elsewhere (e.g. by the UI bundle, to every user).
 *
 * @internal
 */
final class SensitiveDataMasker
{
    public const MASK = '***';

    /**
     * Names of the HTTP headers and XML elements whose value is masked.
     */
    private const SENSITIVE_NAME_PATTERN = 'authorization|cookie|password|passwd|secret|token|api[-_.]?key|signature|credential|session';

    /**
     * @param array<string, ?string> $trace LastRequest, LastRequestHeaders, LastResponse and LastResponseHeaders
     *
     * @return array<string, ?string>
     */
    public static function maskTrace(array $trace): array
    {
        foreach ($trace as $key => $value) {
            $trace[$key] = str_ends_with($key, 'Headers') ? self::maskHttpHeaders($value) : self::maskXml($value);
        }

        return $trace;
    }

    /**
     * The SOAP headers (e.g. WS-Security) are logged by their name only.
     *
     * @param array<mixed> $options
     *
     * @return array<mixed>
     */
    public static function maskOptions(array $options): array
    {
        if (\is_array($options['soap_call_headers'] ?? null)) {
            $options['soap_call_headers'] = array_map(
                static fn (mixed $header): mixed => $header instanceof \SoapHeader ? "{$header->namespace} {$header->name}" : self::MASK,
                $options['soap_call_headers']
            );
        }

        return $options;
    }

    /**
     * Raw HTTP headers, one "Name: value" per line.
     */
    public static function maskHttpHeaders(?string $headers): ?string
    {
        if (null === $headers) {
            return null;
        }

        return preg_replace('/^((?:[\w-]*(?:'.self::SENSITIVE_NAME_PATTERN.')[\w-]*)\s*:)[^\r\n]*/im', '$1 '.self::MASK, $headers);
    }

    /**
     * Text content of the XML elements whose name contains a sensitive word (e.g. wsse:Password, apiKey).
     */
    public static function maskXml(?string $xml): ?string
    {
        if (null === $xml) {
            return null;
        }

        return preg_replace(
            '/(<((?:[\w.-]+:)?[\w.-]*(?:'.self::SENSITIVE_NAME_PATTERN.')[\w.-]*)(?:\s[^>]*)?>)[^<]*(<\/\2>)/i',
            '$1'.self::MASK.'$3',
            $xml
        );
    }
}
