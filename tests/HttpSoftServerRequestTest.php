<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\RequestTestTrait;
use Hamlet\Http\Message\Spec\Traits\ServerRequestTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class HttpSoftServerRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;
    use ServerRequestTestTrait;

    protected function serverRequest(): ServerRequestInterface
    {
        return new \HttpSoft\Message\ServerRequest([], [], [], [], [], 'GET', new \HttpSoft\Message\Uri());
    }

    protected function request(): RequestInterface
    {
        return $this->serverRequest();
    }

    protected function message(): MessageInterface
    {
        return $this->serverRequest();
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
