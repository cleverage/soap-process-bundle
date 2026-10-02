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

namespace CleverAge\SoapProcessBundle\Tests\Transformer;

use CleverAge\SoapProcessBundle\Client\Client;
use CleverAge\SoapProcessBundle\Client\SoapCallOptionsTrait;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use CleverAge\SoapProcessBundle\Tests\Client\FakeSoapClient;
use CleverAge\SoapProcessBundle\Transformer\RequestTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(RequestTransformer::class)]
#[CoversTrait(SoapCallOptionsTrait::class)]
#[UsesClass(Client::class)]
#[UsesClass(ClientRegistry::class)]
class RequestTransformerTest extends TestCase
{
    private Client $client;

    private FakeSoapClient $soapClient;

    private RequestTransformer $transformer;

    protected function setUp(): void
    {
        $this->soapClient = new FakeSoapClient(['GetBook' => 'xsi:type="xsd:string">It', 'IsAvailable' => 'xsi:type="xsd:boolean">false']);
        $this->client = new Client(new NullLogger(), 'test', null);
        $this->client->setSoapClient($this->soapClient);
        $registry = new ClientRegistry();
        $registry->addClient($this->client);
        $this->transformer = new RequestTransformer($registry);
    }

    public function testCode(): void
    {
        self::assertSame('soap_request', $this->transformer->getCode());
    }

    public function testTransform(): void
    {
        self::assertSame('It', $this->transformer->transform(['id' => 1], ['client' => 'test', 'method' => 'GetBook']));
        self::assertFalse($this->transformer->transform(['id' => 1], ['client' => 'test', 'method' => 'IsAvailable']));
    }

    public function testHeadersAreSetForTheCallOnly(): void
    {
        $this->transformer->transform([], [
            'client' => 'test',
            'method' => 'GetBook',
            'soap_call_headers' => ['TransformerToken' => ['namespace' => 'urn:demo', 'data' => 'transformer']],
        ]);
        $this->transformer->transform([], ['client' => 'test', 'method' => 'GetBook']);

        self::assertStringContainsString('<ns1:TransformerToken>transformer</ns1:TransformerToken>', $this->soapClient->requests[0]);
        self::assertStringNotContainsString('TransformerToken', $this->soapClient->requests[1]);
        self::assertNull($this->client->getSoapHeaders());
    }

    public function testFailedCall(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Soap call 'Fail' on client 'test' failed");
        $this->transformer->transform([], ['client' => 'test', 'method' => 'Fail']);
    }

    public function testNonArrayValueIsRejected(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Expecting an array of value');
        $this->transformer->transform('GetBook', ['client' => 'test', 'method' => 'GetBook']);
    }
}
