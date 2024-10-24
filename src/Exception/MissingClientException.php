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

namespace CleverAge\SoapProcessBundle\Exception;

use CleverAge\ProcessBundle\Exception\ProcessExceptionInterface;

/**
 * Exception thrown when trying to fetch a missing Soap client.
 *
 * @author Madeline Veyrenc <mveyrenc@clever-age.com>
 */
class MissingClientException extends \UnexpectedValueException implements ProcessExceptionInterface
{
    /**
     * @param string $code
     */
    public static function create($code): self
    {
        $errorStr = "No Soap client with code : {$code}";

        return new self($errorStr);
    }
}
