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

namespace CleverAge\SoapProcessBundle\Tests;

use CleverAge\SoapProcessBundle\CleverAgeSoapProcessBundle;
use CleverAge\SoapProcessBundle\Client\Client;
use CleverAge\SoapProcessBundle\DependencyInjection\Compiler\RegisterClientsPass;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(CleverAgeSoapProcessBundle::class)]
#[UsesClass(RegisterClientsPass::class)]
#[UsesClass(ClientRegistry::class)]
#[UsesClass(Client::class)]
class CleverAgeSoapProcessBundleTest extends TestCase
{
    public function testPathIsTheBundleRoot(): void
    {
        $path = (new CleverAgeSoapProcessBundle())->getPath();

        self::assertSame(\dirname(__DIR__), $path);
        self::assertDirectoryExists($path.'/config/services');
    }

    public function testTaggedClientsAreRegistered(): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeSoapProcessBundle())->build($container);
        $container->setDefinition('cleverage_soap_process.registry.client', new Definition(ClientRegistry::class))
            ->setPublic(true);
        $container->setDefinition('app.client', new Definition(Client::class, [new Definition(NullLogger::class), 'test', null]))
            ->addTag('cleverage.soap.client');
        $container->compile(true);

        /** @var ClientRegistry $registry */
        $registry = $container->get('cleverage_soap_process.registry.client');
        self::assertSame('test', $registry->getClient('test')->getCode());
    }
}
