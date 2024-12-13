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

namespace CleverAge\SoapProcessBundle\Transformer;

use CleverAge\ProcessBundle\Transformer\ConfigurableTransformerInterface;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type Options array{
 *       'client': string,
 *       'method': string,
 *  }
 */
class RequestTransformer implements ConfigurableTransformerInterface
{
    public function __construct(protected ClientRegistry $registry)
    {
    }

    /**
     * @param array<mixed> $options
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        /** @var Options $options */
        $options = $resolver->resolve($options);

        $client = $this->registry->getClient($options['client']);

        return $client->call($options['method'], $value);
    }

    /**
     * Returns the unique code to identify the transformer.
     */
    public function getCode(): string
    {
        return 'soap_request';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'client',
                'method',
            ]
        );
        $resolver->setAllowedTypes('client', ['string']);
        $resolver->setAllowedTypes('method', ['string']);
    }
}
