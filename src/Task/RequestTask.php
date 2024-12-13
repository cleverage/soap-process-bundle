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

namespace CleverAge\SoapProcessBundle\Task;

use CleverAge\ProcessBundle\Configuration\TaskConfiguration;
use CleverAge\ProcessBundle\Model\AbstractConfigurableTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestTask extends AbstractConfigurableTask
{
    public function __construct(protected LoggerInterface $logger, protected ClientRegistry $registry)
    {
    }

    public function execute(ProcessState $state): void
    {
        $options = $this->getOptions($state);

        $client = $this->registry->getClient($options['client']);

        /** @var array<mixed> $input */
        $input = $state->getInput() ?: [];

        $client->setSoapOptions($this->getOption($state, 'soap_call_options'));
        $client->setSoapHeaders($this->getOption($state, 'soap_call_headers'));

        $result = $client->call($options['method'], $input);

        // Handle empty results
        if (false === $result) {
            $logContext = [
                'options' => $options,
                'last_request' => $client->getLastRequest(),
                'last_request_headers' => $client->getLastRequestHeaders(),
                'last_response' => $client->getLastResponse(),
                'last_response_headers' => $client->getLastResponseHeaders(),
            ];

            $state->setErrorOutput($result);

            $this->logger->error('Empty resultset for query', $logContext);

            if (TaskConfiguration::STRATEGY_SKIP === $state->getTaskConfiguration()->getErrorStrategy()) {
                $state->setSkipped(true);
            } elseif (TaskConfiguration::STRATEGY_STOP === $state->getTaskConfiguration()->getErrorStrategy()) {
                $state->setStopped(true);
            }
        }

        $state->setOutput($result);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'client',
                'method',
            ]
        );
        $resolver->setDefaults(
            [
                'soap_call_options' => null,
                'soap_call_headers' => null,
            ]
        );
        $resolver->setAllowedTypes('client', ['string']);
        $resolver->setAllowedTypes('method', ['string']);
        $resolver->setAllowedTypes('soap_call_options', ['array', 'null']);
        $resolver->setAllowedTypes('soap_call_headers', ['array', 'null']);

        $resolver->setNormalizer('soap_call_headers', function (Options $options, $headers) {
            if (null === $headers) {
                return null;
            }

            $headerResolver = new OptionsResolver();
            $this->configureSoapCallHeaderOption($headerResolver);

            $resolvedHeaders = [];
            foreach ($headers as $name => $header) {
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
}
