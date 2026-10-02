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

use CleverAge\ProcessBundle\Model\AbstractConfigurableTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use CleverAge\SoapProcessBundle\Client\SoapCallOptionsTrait;
use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type RequestOptions array{
 *     'client': string,
 *     'method': string,
 *     'soap_call_options': array<mixed>|null,
 *     'soap_call_headers': array<\SoapHeader>|null,
 *  }
 */
class RequestTask extends AbstractConfigurableTask
{
    use SoapCallOptionsTrait;

    public function __construct(protected LoggerInterface $logger, protected ClientRegistry $registry)
    {
    }

    public function execute(ProcessState $state): void
    {
        /** @var RequestOptions $options */
        $options = $this->getOptions($state);

        $client = $this->registry->getClient($options['client']);

        $input = $state->getInput() ?: [];
        if (!\is_array($input)) {
            throw new \UnexpectedValueException(\sprintf('RequestTask expects an array or empty input, %s given', get_debug_type($input)));
        }

        try {
            $result = $this->callWithSoapOptions($client, $options['method'], $input, $options['soap_call_options'], $options['soap_call_headers']);
        } catch (\SoapFault $e) {
            $logContext = [
                'options' => $options,
                'message' => $e->getMessage(),
                'last_request' => $client->getLastRequest(),
                'last_request_headers' => $client->getLastRequestHeaders(),
                'last_response' => $client->getLastResponse(),
                'last_response_headers' => $client->getLastResponseHeaders(),
            ];

            $this->logger->error('Empty resultset for query', $logContext);

            // The process manager applies the error strategy: the process fails with "stop", the error outputs
            // receive the task input with "skip"
            throw new \RuntimeException(\sprintf("Soap call '%s' on client '%s' failed", $options['method'], $options['client']), 0, $e);
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
        $resolver->setAllowedTypes('client', ['string']);
        $resolver->setAllowedTypes('method', ['string']);

        $this->configureSoapCallOptions($resolver);
    }
}
