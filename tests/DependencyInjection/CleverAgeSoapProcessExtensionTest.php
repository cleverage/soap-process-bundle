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

namespace CleverAge\SoapProcessBundle\Tests\DependencyInjection;

use CleverAge\SoapProcessBundle\Attribute\AsSoapClient;
use CleverAge\SoapProcessBundle\DependencyInjection\CleverAgeSoapProcessExtension;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use CleverAge\SoapProcessBundle\Task\RequestTask;
use CleverAge\SoapProcessBundle\Tests\Fixtures\AttributedClient;
use CleverAge\SoapProcessBundle\Transformer\RequestTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\AttributeAutoconfigurationPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(CleverAgeSoapProcessExtension::class)]
#[UsesClass(RequestTransformer::class)]
#[UsesClass(AsSoapClient::class)]
class CleverAgeSoapProcessExtensionTest extends TestCase
{
    public function testServices(): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeSoapProcessExtension())->load([], $container);

        self::assertSame(ClientRegistry::class, $container->getDefinition('cleverage_soap_process.registry.client')->getClass());
        // Autowirable by its class name
        self::assertSame('cleverage_soap_process.registry.client', (string) $container->getAlias(ClientRegistry::class));
    }

    public function testAsSoapClientAttributeRegistersTheCode(): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeSoapProcessExtension())->load([], $container);
        $container->setDefinition('app.attributed', (new Definition(AttributedClient::class))->setAutoconfigured(true));

        (new AttributeAutoconfigurationPass())->process($container);
        (new ResolveInstanceofConditionalsPass())->process($container);
        (new ResolveChildDefinitionsPass())->process($container);

        self::assertSame([['code' => 'app_attributed']], $container->getDefinition('app.attributed')->getTag('cleverage.soap.client'));
    }

    public function testTaskIsRegistered(): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeSoapProcessExtension())->load([], $container);

        // The class name is the service id
        $task = $container->getDefinition(RequestTask::class);
        // Tasks are stateful: each process execution must get its own instance
        self::assertFalse($task->isShared());
        // Referenced by its code in the process configurations (task: soap_request)
        self::assertSame([['code' => 'soap_request']], $task->getTag('cleverage.task'));
        // Public for the direct access from the service container, deprecated
        self::assertTrue($task->isPublic());
        self::assertSame([['package' => 'cleverage/soap-process-bundle', 'version' => '3.3']], $task->getTag('container.private'));
        // Logged in the process tasks channel: the "channel" of the "monolog.logger" tag only applies to a reference to
        // the "logger" service
        self::assertSame([['channel' => 'cleverage_process_task']], $task->getTag('monolog.logger'));
        $references = [];
        foreach ($task->getArguments() as $argument) {
            if ($argument instanceof Reference) {
                $references[] = (string) $argument;
            }
        }
        self::assertSame(['logger', 'cleverage_soap_process.registry.client'], $references);

        // Former id, deprecated
        $this->assertDeprecatedAlias(
            $container,
            'cleverage_soap_process.task.request',
            RequestTask::class,
            'The "cleverage_soap_process.task.request" service alias is deprecated, use the "soap_request" task code or the class name of the task instead. It will be removed in 4.0.',
        );
    }

    public function testTransformerIsRegistered(): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeSoapProcessExtension())->load([], $container);

        // The class name is the service id
        $transformer = $container->getDefinition(RequestTransformer::class);
        // Loaded lazily by its code, which must be the one returned by getCode()
        $code = (new RequestTransformer(new ClientRegistry()))->getCode();
        self::assertSame('soap_request', $code);
        self::assertSame([['code' => $code]], $transformer->getTag('cleverage.transformer'));
        // Public for the direct access from the service container, deprecated
        self::assertTrue($transformer->isPublic());
        self::assertSame([['package' => 'cleverage/soap-process-bundle', 'version' => '3.3']], $transformer->getTag('container.private'));
        self::assertSame([['channel' => 'cleverage_process_transformer']], $transformer->getTag('monolog.logger'));
        $arguments = $transformer->getArguments();
        self::assertCount(1, $arguments);
        self::assertInstanceOf(Reference::class, $arguments[0]);
        self::assertSame('cleverage_soap_process.registry.client', (string) $arguments[0]);

        // Former id, deprecated
        $this->assertDeprecatedAlias(
            $container,
            'cleverage_soap_process.transformer.request',
            RequestTransformer::class,
            'The "cleverage_soap_process.transformer.request" service alias is deprecated, use the "soap_request" transformer code or the class name of the transformer instead. It will be removed in 4.0.',
        );
    }

    private function assertDeprecatedAlias(ContainerBuilder $container, string $id, string $target, string $message): void
    {
        $alias = $container->getAlias($id);
        self::assertSame($target, (string) $alias);
        self::assertFalse($alias->isPublic());
        self::assertTrue($alias->isDeprecated());
        self::assertSame(
            ['package' => 'cleverage/soap-process-bundle', 'version' => '3.3', 'message' => $message],
            $alias->getDeprecation($id)
        );
    }
}
