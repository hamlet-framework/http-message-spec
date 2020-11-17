<?php

namespace Hamlet\Http\Message;

use Hamlet\Http\Message\Spec\Traits\DataProviderTrait;
use Hamlet\Http\Message\Spec\Traits\UploadedFileTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class HamletUploadedFileTest extends TestCase
{
    use DataProviderTrait;
    use UploadedFileTestTrait;

    protected function stream($data): StreamInterface
    {
        if (is_resource($data)) {
            return Stream::fromResource($data);
        }
        if (is_string($data)) {
            return Stream::fromString($data);
        }
    }

    protected function uploadedFile($streamOrResource, $size, $error, $clientFileName = null, $clientMediaType = null): UploadedFileInterface
    {
        $builder = UploadedFile::builder();
        if ($streamOrResource instanceof StreamInterface) {
            $builder->withStream($streamOrResource);
        } elseif (is_resource($streamOrResource)) {
            $builder->withResource($streamOrResource);
        } elseif (is_string($streamOrResource)) {
            $builder->withPath($streamOrResource);
        }
        $builder->withSize($size);
        $builder->withErrorStatus($error);
        $builder->withClientFileName($clientFileName);
        $builder->withClientMediaType($clientMediaType);
        return $builder->build();
    }
}
