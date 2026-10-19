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

namespace CleverAge\SoapProcessBundle\DependencyInjection;

use CleverAge\SoapProcessBundle\Attribute\AsSoapClient;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see https://symfony.com/doc/current/bundles/extension.html
 */
class CleverAgeSoapProcessExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->findServices($container, __DIR__.'/../../config/services');

        $container->registerAttributeForAutoconfiguration(
            AsSoapClient::class,
            static function (ChildDefinition $definition, AsSoapClient $attribute): void {
                $definition->addTag('cleverage.soap.client', ['code' => $attribute->code]);
            }
        );
    }

    /**
     * Import the service definition files of a directory into the container.
     */
    protected function findServices(ContainerBuilder $container, string $path, string $extension = 'yaml'): void
    {
        $loader = new YamlFileLoader($container, new FileLocator($path));
        $loader->import('*.'.$extension, 'glob');
    }
}
