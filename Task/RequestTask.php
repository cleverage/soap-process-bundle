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
        $resolver->setAllowedTypes('client', ['string']);
        $resolver->setAllowedTypes('method', ['string']);
    }
}
