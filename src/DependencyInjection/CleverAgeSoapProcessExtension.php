<?php declare(strict_types=1);
/**
 * This file is part of the CleverAge/SoapProcessBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\SoapProcessBundle\DependencyInjection;

use Sidus\BaseBundle\DependencyInjection\SidusBaseExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class CleverAgeSoapProcessExtension extends SidusBaseExtension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->findServices($container, __DIR__.'/../Resources/config/services');
    }

    /**
     * Recursively import config files into container.
     */
    protected function findServices(ContainerBuilder $container, string $path, string $extension = 'yaml'): void
    {
        $finder = new Finder();
        $finder->in($path)
            ->name('*.'.$extension)->files();
        $loader = new YamlFileLoader($container, new FileLocator($path));
        foreach ($finder as $file) {
            $loader->load($file->getFilename());
        }
    }
}
