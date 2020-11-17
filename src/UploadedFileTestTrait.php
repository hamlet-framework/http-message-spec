<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

trait UploadedFileTestTrait
{
    abstract protected function stream($data): StreamInterface;

    abstract protected function uploadedFile($streamOrResource, $size, $error, $clientFileName = null, $clientMediaType = null): UploadedFileInterface;

    /**
     * @dataProvider invalid_file_upload_error_statuses
     * @param $status
     */
    public function test_setting_invalid_error_status_raises_exception($status)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->uploadedFile(fopen('php://temp', 'wb+'), 0, $status);
    }

    public function test_setting_stream_returns_original_stream()
    {
        $resource = \fopen('php://temp', 'r');
        $stream = $this->stream($resource);

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        Assert::assertSame($stream, $upload->getStream());
    }

    public function test_setting_resource_returns_wrapped_stream()
    {
        $stream = fopen('php://temp', 'wb+');
        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);

        $uploadStream = $upload->getStream()->detach();
        Assert::assertSame($stream, $uploadStream);
    }

    public function test_move_file_to_designated_path()
    {
        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);

        $to = tempnam(sys_get_temp_dir(), 'target');
        $upload->moveTo($to);
        Assert::assertTrue(file_exists($to));
        $contents = file_get_contents($to);
        Assert::assertSame((string) $stream, $contents);
    }

    /**
     * @dataProvider invalid_target_paths
     * @param $path
     */
    public function test_moving_to_invalid_path_raises_exception($path)
    {
        $this->expectException(InvalidArgumentException::class);

        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $upload->moveTo($path);
    }

    public function test_move_cannot_be_called_more_than_once()
    {
        $this->expectException(RuntimeException::class);

        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        Assert::assertTrue(file_exists($to));
        $upload->moveTo($to);
    }

    public function test_cannot_retrieve_stream_after_move()
    {
        $this->expectException(RuntimeException::class);

        $stream = $this->stream(fopen('php://temp', 'wb+'));
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        Assert::assertTrue(file_exists($to));
        $upload->getStream();
    }

    public function test_move_to_creates_stream_only_path_provided()
    {
        $source = tempnam(sys_get_temp_dir(), 'source');
        $target = tempnam(sys_get_temp_dir(), 'target');

        $content = md5(rand(1, 10000) . time());
        file_put_contents($source, $content);

        $uploadedFile = $this->uploadedFile($source, 100, UPLOAD_ERR_OK, basename(__FILE__), 'text/plain');
        $uploadedFile->moveTo($target);
        Assert::assertSame($content, file_get_contents($target));
    }

    /**
     * @dataProvider file_upload_error_codes
     * @param int $code
     */
    public function test_non_ok_error_code_raises_exception_on_get_stream($code)
    {
        $this->expectException(RuntimeException::class);

        $source = tempnam(sys_get_temp_dir(), 'source');

        $uploadedFile = $this->uploadedFile($source, 100, $code);
        $uploadedFile->getStream();
    }

    /**
     * @dataProvider file_upload_error_codes
     * @param int $code
     */
    public function test_non_ok_error_code_raises_exception_on_move_to($code)
    {
        $this->expectException(RuntimeException::class);

        $source = tempnam(sys_get_temp_dir(), 'source');

        $uploadedFile = $this->uploadedFile($source, 100, $code);
        $uploadedFile->moveTo('/tmp/foo');
    }

    /**
     * @dataProvider invalid_file_sizes
     * @param $size
     */
    public function test_setting_invalid_file_size_raises_exception($size)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uploadedFile(fopen('php://temp', 'wb+'), $size, UPLOAD_ERR_OK);
    }

    /**
     * @dataProvider invalid_file_names
     * @param $fileName
     */
    public function test_invalid_client_file_names_raise_an_exception($fileName)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, $fileName);
    }

    /**
     * @dataProvider valid_media_types
     * @param $mediaType
     */
    public function test_valid_media_types_are_accepted($mediaType)
    {
        $file = $this->uploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'foobar.baz', $mediaType);
        Assert::assertSame($mediaType, $file->getClientMediaType());
    }

    /**
     * @dataProvider invalid_media_types
     * @param $mediaType
     */
    public function test_invalid_client_media_type_raise_an_exception($mediaType)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'foobar.baz', $mediaType);
    }
}
