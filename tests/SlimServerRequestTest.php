<?php

namespace Hamlet\Http\Message;

use function GuzzleHttp\Psr7\stream_for;
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
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Uri;

class SlimServerRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;
    use ServerRequestTestTrait;

    protected function serverRequest(): ServerRequestInterface
    {
        return new Request('GET', Uri::createFromString(''), new Headers, [], [], stream_for());
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
        return Uri::createFromString($value);
    }
}
