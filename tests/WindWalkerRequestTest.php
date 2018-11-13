<?php

namespace Hamlet\Http\Message;

use GuzzleHttp\Psr7\Uri;
use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\RequestTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Stream\Stream;

class WindWalkerRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;

    protected function request(): RequestInterface
    {
        return new Request();
    }

    protected function message(): MessageInterface
    {
        return $this->request();
    }

    protected function stream(): StreamInterface
    {
        return new Stream();
    }

    protected function uri(string $value): UriInterface
    {
        return new Uri();
    }
}
