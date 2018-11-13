<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\RequestTestTrait;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Stream;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class NyholmRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;

    protected function message(): MessageInterface
    {
        return new Request('GET', new Uri());
    }

    protected function stream(): StreamInterface
    {
        return Stream::create();
    }

    protected function request(): RequestInterface
    {
        return new Request('GET', new Uri());
    }

    protected function uri(string $value): UriInterface
    {
        return new Uri($value);
    }
}
