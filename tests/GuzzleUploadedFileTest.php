<?php

namespace Hamlet\Http\Message;

use GuzzleHttp\Psr7\Utils;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\Psr7\UploadedFile;
use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\UploadedFileTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class GuzzleUploadedFileTest extends TestCase
{
    use DataProviderTrait;
    use UploadedFileTestTrait;

    protected function stream($data): StreamInterface
    {
        return Utils::streamFor($data);
    }
    protected function uploadedFile($streamOrResource, $size, $error, $clientFileName = null, $clientMediaType = null): UploadedFileInterface
    {
        return new UploadedFile($streamOrResource, $size, $error, $clientFileName, $clientMediaType);
    }
}
