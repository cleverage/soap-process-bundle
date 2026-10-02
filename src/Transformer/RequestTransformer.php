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
use CleverAge\SoapProcessBundle\Client\SoapCallOptionsTrait;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type TransformerOptions array{
 *       'client': string,
 *       'method': string,
 *       'soap_call_options': array<mixed>|null,
 *       'soap_call_headers': array<\SoapHeader>|null,
 *  }
 */
class RequestTransformer implements ConfigurableTransformerInterface
{
    use SoapCallOptionsTrait;

    public function __construct(protected ClientRegistry $registry)
    {
    }

    /**
     * @param array<mixed> $options
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        if (!\is_array($value)) {
            throw new \UnexpectedValueException('Expecting an array of value');
        }

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        /** @var TransformerOptions $options */
        $options = $resolver->resolve($options);

        $client = $this->registry->getClient($options['client']);

        try {
            return $this->callWithSoapOptions($client, $options['method'], $value, $options['soap_call_options'], $options['soap_call_headers']);
        } catch (\SoapFault $e) {
            throw new \RuntimeException(\sprintf("Soap call '%s' on client '%s' failed", $options['method'], $options['client']), 0, $e);
        }
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

        $this->configureSoapCallOptions($resolver);
    }
}
