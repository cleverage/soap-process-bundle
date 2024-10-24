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

namespace CleverAge\SoapProcessBundle\Registry;

use CleverAge\SoapProcessBundle\Client\ClientInterface;
use CleverAge\SoapProcessBundle\Exception\MissingClientException;

/**
 * Holds all tagged soap client services.
 *
 * @author Madeline Veyrenc <mveyrenc@clever-age.com>
 */
class ClientRegistry
{
    /** @var ClientInterface[] */
    private array $clients = [];

    public function addClient(ClientInterface $client): void
    {
        if (\array_key_exists($client->getCode(), $this->getClients())) {
            throw new \UnexpectedValueException("Client {$client->getCode()} is already defined");
        }
        $this->clients[$client->getCode()] = $client;
    }

    /**
     * @return ClientInterface[]
     */
    public function getClients(): array
    {
        return $this->clients;
    }

    /**
     * @throws MissingClientException
     */
    public function getClient(string $code): ClientInterface
    {
        if (!$this->hasClient($code)) {
            throw MissingClientException::create($code);
        }

        return $this->getClients()[$code];
    }

    public function hasClient(string $code): bool
    {
        return \array_key_exists($code, $this->getClients());
    }
}
