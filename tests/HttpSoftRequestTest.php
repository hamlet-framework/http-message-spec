<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\RequestTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class HttpSoftRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;

    protected function request(): RequestInterface
    {
        return new \HttpSoft\Message\Request('GET', new \HttpSoft\Message\Uri());
    }

    protected function message(): MessageInterface
    {
        return $this->request();
    }

    protected function stream(): StreamInterface
    {
        $factory = new \HttpSoft\Message\StreamFactory();
        return $factory->createStream('');
    }

    protected function uri(string $value): UriInterface
    {
        return new \HttpSoft\Message\Uri($value);
    }
}
