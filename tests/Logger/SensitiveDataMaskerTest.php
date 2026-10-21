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

namespace CleverAge\SoapProcessBundle\Tests\Logger;

use CleverAge\SoapProcessBundle\Logger\SensitiveDataMasker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SensitiveDataMasker::class)]
class SensitiveDataMaskerTest extends TestCase
{
    public function testMaskHttpHeaders(): void
    {
        self::assertNull(SensitiveDataMasker::maskHttpHeaders(null));
        self::assertSame(
            "POST /soap HTTP/1.1\r\nHost: soap.test\r\nAuthorization: ***\r\nCookie: ***\r\nX-Api-Key: ***\r\nContent-Type: text/xml\r\n",
            SensitiveDataMasker::maskHttpHeaders("POST /soap HTTP/1.1\r\nHost: soap.test\r\nAuthorization: Basic am9objpzZWNyZXQ=\r\nCookie: PHPSESSID=secret\r\nX-Api-Key: secret\r\nContent-Type: text/xml\r\n")
        );
        self::assertSame("HTTP/1.1 200 OK\nSet-Cookie: ***\n", SensitiveDataMasker::maskHttpHeaders("HTTP/1.1 200 OK\nSet-Cookie: session=secret; HttpOnly\n"));
    }

    public function testMaskXml(): void
    {
        self::assertNull(SensitiveDataMasker::maskXml(null));
        self::assertSame(
            '<wsse:Security><wsse:UsernameToken><wsse:Username>john</wsse:Username><wsse:Password Type="PasswordText">***</wsse:Password></wsse:UsernameToken></wsse:Security><ns1:apiKey>***</ns1:apiKey><ns1:title>It</ns1:title>',
            SensitiveDataMasker::maskXml('<wsse:Security><wsse:UsernameToken><wsse:Username>john</wsse:Username><wsse:Password Type="PasswordText">secret</wsse:Password></wsse:UsernameToken></wsse:Security><ns1:apiKey>secret</ns1:apiKey><ns1:title>It</ns1:title>')
        );
    }

    public function testMaskTrace(): void
    {
        self::assertSame(
            ['LastRequest' => '<Password>***</Password>', 'LastRequestHeaders' => 'Authorization: ***', 'LastResponse' => null, 'LastResponseHeaders' => null],
            SensitiveDataMasker::maskTrace(['LastRequest' => '<Password>secret</Password>', 'LastRequestHeaders' => 'Authorization: Basic secret', 'LastResponse' => null, 'LastResponseHeaders' => null])
        );
    }

    public function testMaskOptions(): void
    {
        self::assertSame(
            ['client' => 'test', 'soap_call_headers' => ['urn:demo Security']],
            SensitiveDataMasker::maskOptions(['client' => 'test', 'soap_call_headers' => [new \SoapHeader('urn:demo', 'Security', ['password' => 'secret'])]])
        );
        self::assertSame(['soap_call_headers' => null], SensitiveDataMasker::maskOptions(['soap_call_headers' => null]));
    }
}
