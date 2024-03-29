<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\UploadedFileTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;

class LaminasDiactorosUploadedFileTest extends TestCase
{
    use DataProviderTrait;
    use UploadedFileTestTrait;

    protected function stream($data): StreamInterface
    {
        return new Stream($data);
    }

    protected function uploadedFile($streamOrResource, $size, $error, $clientFileName = null, $clientMediaType = null): UploadedFileInterface
    {
        return new UploadedFile($streamOrResource, $size, $error, $clientFileName, $clientMediaType);
    }
}
