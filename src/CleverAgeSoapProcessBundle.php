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

namespace CleverAge\SoapProcessBundle;

use CleverAge\ProcessBundle\DependencyInjection\Compiler\RegistryCompilerPass;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class CleverAgeSoapProcessBundle.
 *
 * @author  Valentin Clavreul <vclavreul@clever-age.com>
 * @author  Vincent Chalnot <vchalnot@clever-age.com>
 * @author  Madeline Veyrenc <mveyrenc@clever-age.com>
 */
class CleverAgeSoapProcessBundle extends Bundle
{
    /**
     * Adding compiler passes to inject services into registry.
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new RegistryCompilerPass(
                ClientRegistry::class,
                'cleverage.soap.client',
                'addClient'
            )
        );
    }
}
