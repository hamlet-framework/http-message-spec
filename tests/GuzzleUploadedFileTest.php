<?php

namespace Hamlet\Http\Message;

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
        return stream_for($data);
    }

    protected function uploadedFile($streamOrResource, int $size, int $error, ?string $clientFileName = null, ?string $clientMediaType = null): UploadedFileInterface
    {
        return new UploadedFile($streamOrResource, $size, $error, $clientFileName, $clientMediaType);
    }
}
