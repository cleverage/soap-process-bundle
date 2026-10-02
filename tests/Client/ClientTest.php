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

use CleverAge\SoapProcessBundle\Client\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

#[CoversClass(Client::class)]
class ClientTest extends TestCase
{
    /** @var list<string> */
    private array $logs = [];

    public function testAccessors(): void
    {
        $client = $this->createClient();

        self::assertSame('test', $client->getCode());
        self::assertSame('http://soap.test/?wsdl', $client->getWsdl());
        self::assertSame(['trace' => true], $client->getOptions());
        self::assertNull($client->getSoapOptions());
        self::assertNull($client->getSoapHeaders());

        $header = new \SoapHeader('urn:demo', 'Token', 'secret');
        $client->setWsdl(null);
        $client->setOptions(['exceptions' => true]);
        $client->setSoapOptions(['soapaction' => 'urn:demo#Other']);
        $client->setSoapHeaders([$header]);

        self::assertNull($client->getWsdl());
        self::assertSame(['exceptions' => true], $client->getOptions());
        self::assertSame(['soapaction' => 'urn:demo#Other'], $client->getSoapOptions());
        self::assertSame([$header], $client->getSoapHeaders());
    }

    public function testUndefinedCode(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Client code is not defined');
        (new Client($this->createLogger(), '', null))->getCode();
    }

    public function testCall(): void
    {
        $client = $this->createClient();
        $soapClient = new FakeSoapClient(['GetBook' => 'xsi:type="xsd:string">It']);
        $client->setSoapClient($soapClient);

        self::assertSame('It', $client->call('GetBook', ['id' => 1]));
        self::assertStringContainsString('<ns1:GetBook>', (string) $client->getLastRequest());
        self::assertStringContainsString('<return xsi:type="xsd:string">It</return>', (string) $client->getLastResponse());
        // The trace is logged with the "trace" option
        self::assertSame(["notice: Soap call 'GetBook' on 'http://soap.test/?wsdl'", "debug: Trace of soap call 'GetBook' on 'http://soap.test/?wsdl'"], $this->logs);
    }

    public function testSoapOptionsAndHeadersAreSent(): void
    {
        $client = $this->createClient();
        $soapClient = new FakeSoapClient(['GetBook' => 'xsi:type="xsd:string">It']);
        $client->setSoapClient($soapClient);
        $client->setSoapHeaders([new \SoapHeader('urn:demo', 'Token', 'secret')]);

        $client->call('GetBook', ['id' => 1]);

        self::assertStringContainsString('<ns1:Token>secret</ns1:Token>', $soapClient->requests[0]);
    }

    public function testFalseResult(): void
    {
        $client = $this->createClient();
        $client->setSoapClient(new FakeSoapClient(['IsAvailable' => 'xsi:type="xsd:boolean">false']));

        self::assertFalse($client->call('IsAvailable', ['id' => 1]));
    }

    public function testSoapFaultIsLoggedAndThrown(): void
    {
        $client = $this->createClient();
        $client->setSoapClient(new FakeSoapClient());

        try {
            $client->call('Fail');
            self::fail('The call must fail');
        } catch (\SoapFault $e) {
            self::assertSame('Unknown method', $e->getMessage());
        }
        self::assertSame("alert: Soap call 'Fail' on 'http://soap.test/?wsdl' failed : Unknown method", $this->logs[1]);
        self::assertStringContainsString('Unknown method', (string) $client->getLastResponse());
    }

    public function testSoapFaultIsThrownWithoutExceptions(): void
    {
        $client = $this->createClient();
        // With the "exceptions: false" option, __soapCall() returns the fault
        $client->setSoapClient(new FakeSoapClient([], ['exceptions' => false]));

        $this->expectException(\SoapFault::class);
        $this->expectExceptionMessage('Unknown method');
        $client->call('Fail');
    }

    public function testCallOverride(): void
    {
        $client = new class($this->createLogger(), 'test', null, ['location' => 'http://soap.test/soap', 'uri' => 'urn:demo']) extends Client {
            /**
             * @param array<mixed> $input
             */
            public function soapCallGetBook(array $input): string
            {
                return 'Book '.(\is_scalar($input['id'] ?? null) ? $input['id'] : '');
            }
        };

        self::assertSame('Book 1', $client->call('getBook', ['id' => 1]));
        // The SoapClient is initialized with the client options, and the call is logged before the override
        self::assertInstanceOf(\SoapClient::class, $client->getSoapClient());
        self::assertSame(["notice: Soap call 'getBook' on ''"], $this->logs);
    }

    public function testDoSoapCallWithoutSoapClient(): void
    {
        $client = new class($this->createLogger(), 'test', null) extends Client {
            public function doCall(): mixed
            {
                return $this->doSoapCall('GetBook');
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Soap client is not initialized');
        $client->doCall();
    }

    private function createClient(): Client
    {
        return new Client($this->createLogger(), 'test', 'http://soap.test/?wsdl', ['trace' => true]);
    }

    private function createLogger(): AbstractLogger
    {
        $onLog = function (string $log): void {
            $this->logs[] = $log;
        };

        return new class($onLog) extends AbstractLogger {
            public function __construct(private readonly \Closure $onLog)
            {
            }

            /**
             * @param array<mixed> $context
             */
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                ($this->onLog)(\sprintf('%s: %s', \is_string($level) ? $level : '', $message));
            }
        };
    }
}
