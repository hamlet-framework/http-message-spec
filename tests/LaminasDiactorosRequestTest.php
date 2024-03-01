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
use Laminas\Diactoros\Request;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\Uri;

class LaminasDiactorosRequestTest extends TestCase
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
        $factory = new StreamFactory();
        return $factory->createStream();
    }

    protected function uri(string $value): UriInterface
    {
        return new Uri();
    }
}
