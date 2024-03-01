<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\MessageTestTrait;
use Hamlet\Http\Message\Spec\Traits\ResponseTestTrait;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class HamletResponseTest // extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use ResponseTestTrait;

    protected function response(): ResponseInterface
    {
        return Response::empty();
    }

    protected function message(): MessageInterface
    {
        return $this->response();
    }

    protected function stream(): StreamInterface
    {
        return Stream::empty();
    }

    public function test_non_validating_builder_sets_values()
    {
        $response = Response::nonValidatingBuilder()
            ->withStatus(200, 'OK')
            ->build();

        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('OK', $response->getReasonPhrase());
    }
}
