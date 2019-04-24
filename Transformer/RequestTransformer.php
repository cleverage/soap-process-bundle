<?php declare(strict_types=1);
/**
 * This file is part of the CleverAge/ProcessBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\SoapProcessBundle\Transformer;

use CleverAge\SoapProcessBundle\Registry\ClientRegistry;
use CleverAge\ProcessBundle\Transformer\ConfigurableTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RequestTransformer
 *
 * @author Madeline Veyrenc <mveyrenc@clever-age.com>
 */
class RequestTransformer implements ConfigurableTransformerInterface
{
    /** @var ClientRegistry */
    protected $registry;

    /**
     * RequestTransformer constructor.
     *
     * @param ClientRegistry $registry
     */
    public function __construct(ClientRegistry $registry)
    {
        $this->registry = $registry;
    }


    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        $client = $this->registry->getClient($options['client']);

        return $client->call($options['method'], $value);
    }

    /**
     * Returns the unique code to identify the transformer
     *
     * @return string
     */
    public function getCode(): string
    {
        return 'soap_request';
    }

    /**
     * {@inheritdoc}
     */
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
