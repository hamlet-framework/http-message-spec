<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\StreamTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Slim\Http\Stream;

class SlimStreamTest extends TestCase
{
    use DataProviderTrait;
    use StreamTestTrait;

    protected function stream($handle): StreamInterface
    {
        return new Stream($handle);
    }
}
