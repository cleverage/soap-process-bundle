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

namespace CleverAge\SoapProcessBundle\Tests\Client;

/**
 * Non-WSDL SoapClient (urn:demo) answering prepared responses, without any network access.
 */
class FakeSoapClient extends \SoapClient
{
    /** @var list<string> Requests sent, in order */
    public array $requests = [];

    /**
     * @param array<string, string> $results XML results (content of <return>) indexed by method, a missing method
     *                                       answers a SOAP fault
     * @param array<string, mixed>  $options
     */
    public function __construct(private readonly array $results = [], array $options = [])
    {
        parent::__construct(null, $options + ['location' => 'http://soap.test/soap', 'uri' => 'urn:demo', 'trace' => true]);
    }

    #[\Override]
    public function __doRequest(string $request, string $location, string $action, int $version, bool $oneWay = false, mixed ...$extra): ?string
    {
        $this->requests[] = $request;
        $method = substr($action, (int) strrpos($action, '#') + 1);
        $body = \array_key_exists($method, $this->results)
            ? "<ns1:{$method}Response><return {$this->results[$method]}</return></ns1:{$method}Response>"
            : '<SOAP-ENV:Fault><faultcode>SOAP-ENV:Server</faultcode><faultstring>Unknown method</faultstring></SOAP-ENV:Fault>';

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="urn:demo"'
            .' xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            ."<SOAP-ENV:Body>{$body}</SOAP-ENV:Body></SOAP-ENV:Envelope>";
    }
}
