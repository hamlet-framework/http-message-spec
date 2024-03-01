<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use function fopen;

trait UploadedFileTestTrait
{
    abstract protected function stream($data): StreamInterface;

    abstract protected function uploadedFile($streamOrResource, $size, $error, $clientFileName = null, $clientMediaType = null): UploadedFileInterface;

    #[DataProvider('invalid_file_upload_error_statuses')] public function test_setting_invalid_error_status_raises_exception(mixed $status): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->uploadedFile(fopen('php://temp', 'wb+'), 0, $status);
    }

    public function test_setting_stream_returns_original_stream(): void
    {
        $resource = fopen('php://temp', 'r');
        $stream = $this->stream($resource);

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $this->assertSame($stream, $upload->getStream());
    }

    public function test_setting_resource_returns_wrapped_stream(): void
    {
        $stream = fopen('php://temp', 'wb+');
        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);

        $uploadStream = $upload->getStream()->detach();
        $this->assertSame($stream, $uploadStream);
    }

    public function test_move_file_to_designated_path(): void
    {
        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);

        $to = tempnam(sys_get_temp_dir(), 'target');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));
        $contents = file_get_contents($to);
        $this->assertSame((string) $stream, $contents);
    }

    #[DataProvider('invalid_target_paths')] public function test_moving_to_invalid_path_raises_exception(mixed $path): void
    {
        $this->expectException(InvalidArgumentException::class);

        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $upload->moveTo($path);
    }

    public function test_move_cannot_be_called_more_than_once(): void
    {
        $this->expectException(RuntimeException::class);

        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));
        $upload->moveTo($to);
    }

    public function test_cannot_retrieve_stream_after_move(): void
    {
        $this->expectException(RuntimeException::class);

        $stream = $this->stream(fopen('php://temp', 'wb+'));
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));
        $upload->getStream();
    }

    public function test_move_to_creates_stream_only_path_provided(): void
    {
        $source = tempnam(sys_get_temp_dir(), 'source');
        $target = tempnam(sys_get_temp_dir(), 'target');

        $content = md5(rand(1, 10000) . time());
        file_put_contents($source, $content);

        $uploadedFile = $this->uploadedFile($source, 100, UPLOAD_ERR_OK, basename(__FILE__), 'text/plain');
        $uploadedFile->moveTo($target);
        $this->assertSame($content, file_get_contents($target));
    }

    #[DataProvider('file_upload_error_codes')] public function test_non_ok_error_code_raises_exception_on_get_stream(int $code): void
    {
        $this->expectException(RuntimeException::class);

        $source = tempnam(sys_get_temp_dir(), 'source');

        $uploadedFile = $this->uploadedFile($source, 100, $code);
        $uploadedFile->getStream();
    }

    #[DataProvider('file_upload_error_codes')] public function test_non_ok_error_code_raises_exception_on_move_to(int $code): void
    {
        $this->expectException(RuntimeException::class);

        $source = tempnam(sys_get_temp_dir(), 'source');

        $uploadedFile = $this->uploadedFile($source, 100, $code);
        $uploadedFile->moveTo('/tmp/foo');
    }

    #[DataProvider('invalid_file_sizes')] public function test_setting_invalid_file_size_raises_exception(mixed $size): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uploadedFile(fopen('php://temp', 'wb+'), $size, UPLOAD_ERR_OK);
    }

    #[DataProvider('invalid_file_names')] public function test_invalid_client_file_names_raise_an_exception(mixed $fileName): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, $fileName);
    }

    #[DataProvider('valid_media_types')] public function test_valid_media_types_are_accepted(mixed $mediaType): void
    {
        $file = $this->uploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'foobar.baz', $mediaType);
        $this->assertSame($mediaType, $file->getClientMediaType());
    }

    #[DataProvider('invalid_media_types')] public function test_invalid_client_media_type_raise_an_exception(mixed $mediaType): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'foobar.baz', $mediaType);
    }
}
