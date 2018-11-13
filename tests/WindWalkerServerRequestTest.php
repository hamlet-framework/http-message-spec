<?php

namespace Hamlet\Http\Message;

use GuzzleHttp\Psr7\Uri;
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
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\Stream\Stream;

class WindWalkerServerRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;
    use ServerRequestTestTrait;

    protected function request(): RequestInterface
    {
        return $this->serverRequest();
    }

    protected function serverRequest(): ServerRequestInterface
    {
        return new ServerRequest();
    }

    protected function message(): MessageInterface
    {
        return $this->serverRequest();
    }

    protected function stream(): StreamInterface
    {
        return new Stream();
    }

    protected function uri(string $value = ''): UriInterface
    {
        return new Uri($value);
    }
}
