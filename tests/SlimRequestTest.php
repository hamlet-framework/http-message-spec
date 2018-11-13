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
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Uri;
use function GuzzleHttp\Psr7\stream_for;

class SlimRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;

    protected function request(): RequestInterface
    {
        return new Request('GET', Uri::createFromString(''), new Headers, [], [], stream_for());
    }

    protected function message(): MessageInterface
    {
        return $this->request();
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
