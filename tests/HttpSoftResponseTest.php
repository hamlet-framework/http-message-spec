<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\ResponseTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class HttpSoftResponseTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use ResponseTestTrait;

    protected function response(): ResponseInterface
    {
        return new \HttpSoft\Message\Response();
    }

    protected function message(): MessageInterface
    {
        return $this->response();
    }

    protected function stream(): StreamInterface
    {
        $factory = new \HttpSoft\Message\StreamFactory();
        return $factory->createStream('');
    }
}
