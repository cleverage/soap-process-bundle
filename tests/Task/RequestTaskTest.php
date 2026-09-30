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

use CleverAge\ProcessBundle\Model\ProcessState;
use CleverAge\SoapProcessBundle\Client\ClientInterface;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use CleverAge\SoapProcessBundle\Task\RequestTask;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(RequestTask::class)]
#[UsesClass(ClientRegistry::class)]
class RequestTaskTest extends TestCase
{
    public function testExecute(): void
    {
        $response = new \stdClass();
        $state = $this->createState();
        $state->expects($this->once())->method('setOutput')->with($response);

        $task = $this->createTask($response);
        $task->initialize($state);
        $task->execute($state);
    }

    public function testFailedCallThrows(): void
    {
        $state = $this->createState();
        $state->expects($this->never())->method('setOutput');
        $state->expects($this->never())->method('setErrorOutput');
        $state->expects($this->never())->method('setStopped');

        $task = $this->createTask(false);
        $task->initialize($state);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Soap call 'FullCountryInfo' on client 'test' failed");
        $task->execute($state);
    }

    private function createTask(mixed $callResult): RequestTask
    {
        $client = $this->createStub(ClientInterface::class);
        $client->method('getCode')->willReturn('test');
        $client->method('call')->willReturn($callResult);

        $registry = new ClientRegistry();
        $registry->addClient($client);

        return new RequestTask(new NullLogger(), $registry);
    }

    private function createState(): ProcessState&MockObject
    {
        $state = $this->createMock(ProcessState::class);
        $state->method('getContextualizedOptions')->willReturn([
            'client' => 'test',
            'method' => 'FullCountryInfo',
        ]);
        $state->method('getInput')->willReturn(['sCountryISOCode' => 'FR']);

        return $state;
    }
}
