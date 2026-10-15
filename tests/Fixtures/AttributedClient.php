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

namespace CleverAge\SoapProcessBundle\Tests\Fixtures;

use CleverAge\SoapProcessBundle\Attribute\AsSoapClient;
use CleverAge\SoapProcessBundle\Client\Client;

#[AsSoapClient(code: 'app_attributed')]
final class AttributedClient extends Client
{
}
