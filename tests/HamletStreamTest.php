<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\StreamTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class HamletStreamTest // extends TestCase
{
    use DataProviderTrait;
    use StreamTestTrait;

    protected function stream($handle): StreamInterface
    {
        return Stream::fromResource($handle);
    }
}
