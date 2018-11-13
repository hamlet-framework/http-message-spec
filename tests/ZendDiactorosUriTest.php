<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\UriTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;

class ZendDiactorosUriTest extends TestCase
{
    use DataProviderTrait;
    use UriTestTrait;

    protected function uri($value = ''): UriInterface
    {
        return new Uri($value);
    }
}
