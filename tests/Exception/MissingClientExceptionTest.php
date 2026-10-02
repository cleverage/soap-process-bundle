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

namespace CleverAge\SoapProcessBundle\Tests\Exception;

use CleverAge\SoapProcessBundle\Exception\MissingClientException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MissingClientException::class)]
class MissingClientExceptionTest extends TestCase
{
    public function testCreate(): void
    {
        self::assertSame('No Soap client with code : test', MissingClientException::create('test')->getMessage());
    }
}
