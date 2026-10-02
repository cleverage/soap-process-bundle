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

namespace CleverAge\SoapProcessBundle\Client;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SOAP options and headers of a single call, used by the RequestTask and the soap_request transformer.
 *
 * The options and headers are a state of the (shared) client: they are set for the call only, then the previous
 * ones (e.g. set by the client definition) are restored.
 */
trait SoapCallOptionsTrait
{
    protected function configureSoapCallOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'soap_call_options' => null,
                'soap_call_headers' => null,
            ]
        );
        $resolver->setAllowedTypes('soap_call_options', ['array', 'null']);
        $resolver->setAllowedTypes('soap_call_headers', ['array', 'null']);

        $resolver->setNormalizer('soap_call_headers', function (Options $options, $headers) {
            if (null === $headers) {
                return null;
            }

            $headerResolver = new OptionsResolver();
            $this->configureSoapCallHeaderOption($headerResolver);

            $resolvedHeaders = [];
            /** @var array<string, array<mixed>> $headers */
            foreach ($headers as $name => $header) {
                /** @var array{'namespace': string, 'data': array<mixed>} $resolvedHeader */
                $resolvedHeader = $headerResolver->resolve($header);
                $resolvedHeaders[] = new \SoapHeader($resolvedHeader['namespace'], $name, $resolvedHeader['data']);
            }

            return $resolvedHeaders;
        });
    }

    protected function configureSoapCallHeaderOption(OptionsResolver $resolver): void
    {
        $resolver->setRequired('namespace');
        $resolver->setRequired('data');
    }

    /**
     * @param array<mixed>            $input
     * @param array<mixed>|null       $soapCallOptions null to keep the options of the client
     * @param array<\SoapHeader>|null $soapCallHeaders null to keep the headers of the client
     */
    protected function callWithSoapOptions(
        ClientInterface $client,
        string $method,
        array $input,
        ?array $soapCallOptions,
        ?array $soapCallHeaders,
    ): mixed {
        $clientOptions = $client->getSoapOptions();
        $clientHeaders = $client->getSoapHeaders();
        if (null !== $soapCallOptions) {
            $client->setSoapOptions($soapCallOptions);
        }
        if (null !== $soapCallHeaders) {
            $client->setSoapHeaders($soapCallHeaders);
        }

        try {
            return $client->call($method, $input);
        } finally {
            $client->setSoapOptions($clientOptions);
            $client->setSoapHeaders($clientHeaders);
        }
    }
}
