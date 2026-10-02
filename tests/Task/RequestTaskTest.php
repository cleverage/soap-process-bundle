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

namespace CleverAge\SoapProcessBundle\Tests\Task;

use CleverAge\ProcessBundle\Configuration\ProcessConfiguration;
use CleverAge\ProcessBundle\Configuration\TaskConfiguration;
use CleverAge\ProcessBundle\Context\ContextualOptionResolver;
use CleverAge\ProcessBundle\Model\ProcessHistory;
use CleverAge\ProcessBundle\Model\ProcessState;
use CleverAge\SoapProcessBundle\Client\Client;
use CleverAge\SoapProcessBundle\Client\SoapCallOptionsTrait;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use CleverAge\SoapProcessBundle\Task\RequestTask;
use CleverAge\SoapProcessBundle\Tests\Client\FakeSoapClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

#[CoversClass(RequestTask::class)]
#[CoversTrait(SoapCallOptionsTrait::class)]
#[UsesClass(Client::class)]
#[UsesClass(ClientRegistry::class)]
class RequestTaskTest extends TestCase
{
    /** @var list<array{level: string, message: string, context: array<mixed>}> */
    private array $logs = [];

    private Client $client;

    private FakeSoapClient $soapClient;

    protected function setUp(): void
    {
        $this->soapClient = new FakeSoapClient([
            'GetBook' => 'xsi:type="xsd:string">It',
            'IsAvailable' => 'xsi:type="xsd:boolean">false',
        ]);
        $this->client = new Client($this->createLogger(), 'test', null);
        $this->client->setSoapClient($this->soapClient);
        // Set by the client definition (e.g. with "calls")
        $this->client->setSoapHeaders([new \SoapHeader('urn:demo', 'ClientToken', 'client')]);
    }

    public function testOutputsTheResult(): void
    {
        [$task, $state] = $this->createTask([]);
        $state->setInput(['id' => 1]);

        $task->execute($state);

        self::assertSame('It', $state->getOutput());
        self::assertStringContainsString('<param0 xsi:type="xsd:int">1</param0>', $this->soapClient->requests[0]);
    }

    public function testFalseResultIsOutput(): void
    {
        [$task, $state] = $this->createTask(['method' => 'IsAvailable']);

        $task->execute($state);

        self::assertFalse($state->getOutput());
    }

    public function testClientHeadersAreKept(): void
    {
        [$task, $state] = $this->createTask([]);

        $task->execute($state);

        self::assertStringContainsString('<ns1:ClientToken>client</ns1:ClientToken>', $this->soapClient->requests[0]);
    }

    public function testTaskHeadersAndOptionsAreSetForTheCallOnly(): void
    {
        [$task, $state] = $this->createTask([
            'soap_call_options' => ['soapaction' => 'urn:demo#GetBook'],
            'soap_call_headers' => ['TaskToken' => ['namespace' => 'urn:demo', 'data' => 'task']],
        ]);

        $task->execute($state);

        self::assertStringContainsString('<ns1:TaskToken>task</ns1:TaskToken>', $this->soapClient->requests[0]);
        self::assertStringNotContainsString('ClientToken', $this->soapClient->requests[0]);
        // The client options and headers are restored
        self::assertNull($this->client->getSoapOptions());
        $headers = $this->client->getSoapHeaders();
        self::assertIsArray($headers);
        self::assertSame('ClientToken', $headers[0]->name);
    }

    public function testFailedCall(): void
    {
        [$task, $state] = $this->createTask(['method' => 'Fail', 'soap_call_headers' => ['TaskToken' => ['namespace' => 'urn:demo', 'data' => 'task']]]);

        try {
            $task->execute($state);
            self::fail('The task must fail');
        } catch (\RuntimeException $e) {
            self::assertSame("Soap call 'Fail' on client 'test' failed", $e->getMessage());
            self::assertInstanceOf(\SoapFault::class, $e->getPrevious());
        }

        self::assertNull($state->getOutput());
        $errors = array_values(array_filter($this->logs, static fn (array $log): bool => 'error' === $log['level']));
        self::assertCount(1, $errors);
        self::assertSame('Empty resultset for query', $errors[0]['message']);
        self::assertSame('Unknown method', $errors[0]['context']['message']);
        self::assertIsString($errors[0]['context']['last_response']);
        self::assertStringContainsString('Unknown method', $errors[0]['context']['last_response']);
        // The client headers are restored after a failure too
        $headers = $this->client->getSoapHeaders();
        self::assertIsArray($headers);
        self::assertSame('ClientToken', $headers[0]->name);
    }

    public function testNonArrayInputIsRejected(): void
    {
        [$task, $state] = $this->createTask([]);
        $state->setInput('GetBook');

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('RequestTask expects an array or empty input, string given');
        $task->execute($state);
    }

    public function testInvalidHeader(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->createTask(['soap_call_headers' => ['TaskToken' => ['data' => 'task']]]);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{RequestTask, ProcessState}
     */
    private function createTask(array $options): array
    {
        $registry = new ClientRegistry();
        $registry->addClient($this->client);

        $processConfiguration = new ProcessConfiguration('test', []);
        $state = new ProcessState($processConfiguration, new ProcessHistory($processConfiguration));
        $state->setContextualOptionResolver(new ContextualOptionResolver());
        $state->setContext([]);
        $state->reset(true);
        $state->setTaskConfiguration(new TaskConfiguration('request', RequestTask::class, $options + ['client' => 'test', 'method' => 'GetBook']));

        $task = new RequestTask($this->createLogger(), $registry);
        $task->initialize($state);

        return [$task, $state];
    }

    private function createLogger(): AbstractLogger
    {
        $onLog = function (string $level, string $message, array $context): void {
            $this->logs[] = ['level' => $level, 'message' => $message, 'context' => $context];
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
                ($this->onLog)(\is_string($level) ? $level : '', (string) $message, $context);
            }
        };
    }
}
