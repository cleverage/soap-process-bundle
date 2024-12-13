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

use Psr\Log\LoggerInterface;

/**
 * Class Client.
 *
 * @author Madeline Veyrenc <mveyrenc@clever-age.com>
 */
class Client implements ClientInterface
{
    /** @var array<mixed>|null */
    private $soapOptions;

    /** @var \SoapHeader[]|null */
    private $soapHeaders;

    /** @var \SoapClient */
    private $soapClient;

    /** @var string */
    private $lastRequest;

    /** @var string */
    private $lastRequestHeaders;

    /** @var string */
    private $lastResponse;

    /** @var string */
    private $lastResponseHeaders;

    /**
     * Client constructor.
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $code,
        private ?string $wsdl,
        /** @var array<mixed> */
        private array $options = [],
    ) {
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @throws \UnexpectedValueException
     */
    public function getCode(): string
    {
        if ('' === $this->code || '0' === $this->code) {
            throw new \UnexpectedValueException('Client code is not defined');
        }

        return $this->code;
    }

    public function getWsdl(): ?string
    {
        return $this->wsdl;
    }

    public function setWsdl(?string $wsdl): void
    {
        $this->wsdl = $wsdl;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getSoapOptions(): ?array
    {
        return $this->soapOptions;
    }

    public function setSoapOptions(?array $soapOptions = null): void
    {
        $this->soapOptions = $soapOptions;
    }

    /**
     * @return \SoapHeader[]|null
     */
    public function getSoapHeaders(): ?array
    {
        return $this->soapHeaders;
    }

    /**
     * @param \SoapHeader[]|null $soapHeaders
     */
    public function setSoapHeaders(?array $soapHeaders = null): void
    {
        $this->soapHeaders = $soapHeaders;
    }

    public function getSoapClient(): ?\SoapClient
    {
        return $this->soapClient;
    }

    public function setSoapClient(\SoapClient $soapClient): void
    {
        $this->soapClient = $soapClient;
    }

    public function getLastRequest(): ?string
    {
        return $this->lastRequest;
    }

    public function setLastRequest(?string $lastRequest): void
    {
        $this->lastRequest = $lastRequest;
    }

    public function getLastRequestHeaders(): ?string
    {
        return $this->lastRequestHeaders;
    }

    public function setLastRequestHeaders(?string $lastRequestHeaders): void
    {
        $this->lastRequestHeaders = $lastRequestHeaders;
    }

    public function getLastResponse(): ?string
    {
        return $this->lastResponse;
    }

    public function setLastResponse(?string $lastResponse): void
    {
        $this->lastResponse = $lastResponse;
    }

    public function getLastResponseHeaders(): ?string
    {
        return $this->lastResponseHeaders;
    }

    public function setLastResponseHeaders(?string $lastResponseHeaders): void
    {
        $this->lastResponseHeaders = $lastResponseHeaders;
    }

    public function call(string $method, array $input = []): mixed
    {
        $this->initializeSoapClient();

        $callMethod = \sprintf('soapCall%s', ucfirst($method));
        if (method_exists($this, $callMethod)) {
            return $this->$callMethod($input);
        }

        $this->getLogger()->notice(
            \sprintf("Soap call '%s' on '%s'", $method, $this->getWsdl())
        );

        return $this->doSoapCall($method, $input);
    }

    /**
     * @param array<mixed> $input
     *
     * @return bool|mixed
     */
    protected function doSoapCall(string $method, array $input = []): mixed
    {
        if (!$this->getSoapClient() instanceof \SoapClient) {
            throw new \InvalidArgumentException('Soap client is not initialized');
        }
        try {
            $result = $this->getSoapClient()->__soapCall($method, $input, $this->getSoapOptions(), $this->getSoapHeaders());
        } catch (\SoapFault $e) {
            $this->getLastRequestTrace();
            $this->getLogger()->alert(
                \sprintf("Soap call '%s' on '%s' failed : %s", $method, $this->getWsdl(), $e->getMessage()),
                $this->getLastRequestTraceArray()
            );

            return false;
        }

        $this->getLastRequestTrace();

        if (\array_key_exists('trace', $this->getOptions()) && $this->getOptions()['trace']) {
            $this->getLogger()->debug(
                \sprintf("Trace of soap call '%s' on '%s'", $method, $this->getWsdl()),
                $this->getLastRequestTraceArray()
            );
        }

        return $result;
    }

    /**
     * Initialize \SoapClient object.
     */
    protected function initializeSoapClient(): void
    {
        if (!$this->getSoapClient() instanceof \SoapClient) {
            $options = array_merge($this->getOptions(), ['trace' => true]);
            $this->setSoapClient(new \SoapClient($this->getWsdl(), $options));
        }
    }

    protected function getLastRequestTrace(): void
    {
        if ($this->getSoapClient() instanceof \SoapClient) {
            $this->setLastRequest($this->getSoapClient()->__getLastRequest());
            $this->setLastRequestHeaders($this->getSoapClient()->__getLastRequestHeaders());
            $this->setLastResponse($this->getSoapClient()->__getLastResponse());
            $this->setLastResponseHeaders($this->getSoapClient()->__getLastResponseHeaders());
        }
    }

    /**
     * @return array{
     *     'LastRequest': ?string,
     *     'LastRequestHeaders': ?string,
     *     'LastResponse': ?string,
     *     'LastResponseHeaders': ?string
     * }
     */
    protected function getLastRequestTraceArray(): array
    {
        return [
            'LastRequest' => $this->getLastRequest(),
            'LastRequestHeaders' => $this->getLastRequestHeaders(),
            'LastResponse' => $this->getLastResponse(),
            'LastResponseHeaders' => $this->getLastResponseHeaders(),
        ];
    }
}
