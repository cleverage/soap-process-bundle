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

use CleverAge\SoapProcessBundle\DependencyInjection\CleverAgeSoapProcessExtension;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use CleverAge\SoapProcessBundle\Task\RequestTask;
use CleverAge\SoapProcessBundle\Transformer\RequestTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(CleverAgeSoapProcessExtension::class)]
class CleverAgeSoapProcessExtensionTest extends TestCase
{
    public function testServices(): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeSoapProcessExtension())->load([], $container);

        self::assertSame(ClientRegistry::class, $container->getDefinition('cleverage_soap_process.registry.client')->getClass());

        $taskIds = array_keys(array_filter($container->getDefinitions(), static fn ($definition): bool => RequestTask::class === $definition->getClass()));
        self::assertCount(1, $taskIds);
        $task = $container->getDefinition($taskIds[0]);
        // Tasks are stateful: each process execution must get its own instance
        self::assertFalse($task->isShared());
        // Referenced as '@<class>' in process configurations
        self::assertSame($taskIds[0], (string) $container->getAlias(RequestTask::class));
        self::assertTrue($container->getAlias(RequestTask::class)->isPublic());

        $transformers = array_filter($container->getDefinitions(), static fn ($definition): bool => RequestTransformer::class === $definition->getClass());
        self::assertCount(1, $transformers);
        $transformer = current($transformers);
        self::assertInstanceOf(Definition::class, $transformer);
        self::assertTrue($transformer->hasTag('cleverage.transformer'));
    }
}
