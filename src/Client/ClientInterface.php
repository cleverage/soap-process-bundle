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

namespace CleverAge\SoapProcessBundle\Client;

interface ClientInterface
{
    /**
     * Return the code of the client used in client registry.
     */
    public function getCode(): string;

    /**
     * Return the URI of the WSDL file or NULL if working in non-WSDL mode.
     */
    public function getWsdl(): ?string;

    /**
     * Set the URI of the WSDL file or NULL if working in non-WSDL mode.
     */
    public function setWsdl(?string $wsdl): void;

    /**
     * Return the Soap client options.
     *
     * @see http://php.net/manual/en/soapclient.soapclient.php
     *
     * @return array<mixed>
     */
    public function getOptions(): array;

    /**
     * Set the Soap client options.
     *
     * @see http://php.net/manual/en/soapclient.soapclient.php
     *
     * @param array<mixed> $options
     */
    public function setOptions(array $options): void;

    /**
     * Return the Soap call options.
     *
     * @see https://www.php.net/manual/en/soapclient.soapcall.php
     *
     * @return array<mixed>|null
     */
    public function getSoapOptions(): ?array;

    /**
     * Set the Soap call options.
     *
     * @see https://www.php.net/manual/en/soapclient.soapcall.php
     *
     * @param array<mixed>|null $options
     */
    public function setSoapOptions(?array $options = null): void;

    /**
     * Return the Soap call headers.
     *
     * @see https://www.php.net/manual/en/soapclient.soapcall.php
     *
     * @return \SoapHeader[]|null
     */
    public function getSoapHeaders(): ?array;

    /**
     * Set the Soap call headers.
     *
     * @see https://www.php.net/manual/en/soapclient.soapcall.php
     *
     * @param \SoapHeader[]|null $headers
     */
    public function setSoapHeaders(?array $headers = null): void;

    public function getLastRequest(): ?string;

    public function getLastRequestHeaders(): ?string;

    public function getLastResponse(): ?string;

    public function getLastResponseHeaders(): ?string;

    /**
     * Call Soap method.
     *
     * @param array<mixed> $input
     *
     * @return bool|mixed
     */
    public function call(string $method, array $input = []): mixed;
}
