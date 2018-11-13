<?php

namespace Hamlet\Http\Message;

use GuzzleHttp\Psr7\ServerRequest;
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
use function RingCentral\Psr7\stream_for;

class GuzzleServerRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;
    use ServerRequestTestTrait;

    protected function serverRequest(): ServerRequestInterface
    {
        return new ServerRequest('GET', new Uri());
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
        return stream_for();
    }

    protected function uri(string $value): UriInterface
    {
        return new Uri($value);
    }
}
