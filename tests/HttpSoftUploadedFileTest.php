<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\UploadedFileTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class HttpSoftUploadedFileTest extends TestCase
{
    use DataProviderTrait;
    use UploadedFileTestTrait;

    protected function stream($data): StreamInterface
    {
        return new \HttpSoft\Message\Stream($data);
    }

    protected function uploadedFile($streamOrResource, $size, $error, $clientFileName = null, $clientMediaType = null): UploadedFileInterface
    {
        return new \HttpSoft\Message\UploadedFile($streamOrResource, $size, $error, $clientFileName, $clientMediaType);
    }
}
