<?php declare(strict_types=1);
/**
 * This file is part of the CleverAge/ProcessBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
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

/**
 * Class RequestTask
 *
 * @author Madeline Veyrenc <mveyrenc@clever-age.com>
 */
class RequestTask extends AbstractConfigurableTask
{

    /** @var LoggerInterface */
    protected $logger;

    /** @var ClientRegistry */
    protected $registry;

    /**
     * SoapClientTask constructor.
     *
     * @param LoggerInterface $logger
     * @param ClientRegistry  $registry
     */
    public function __construct(LoggerInterface $logger, ClientRegistry $registry)
    {
        $this->logger = $logger;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProcessState $state): void
    {
        $options = $this->getOptions($state);

        $client = $this->registry->getClient($options['client']);

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

            if ($state->getTaskConfiguration()->getErrorStrategy() === TaskConfiguration::STRATEGY_SKIP) {
                $state->setSkipped(true);
            } elseif ($state->getTaskConfiguration()->getErrorStrategy() === TaskConfiguration::STRATEGY_STOP) {
                $state->setStopped(true);
            }
        }

        $state->setOutput($result);
    }

    /**
     * {@inheritdoc}
     */
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
            if ($headers === null) {
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

    protected function configureSoapCallHeaderOption(OptionsResolver $resolver)
    {
        $resolver->setRequired('namespace');
        $resolver->setRequired('data');
    }
}
