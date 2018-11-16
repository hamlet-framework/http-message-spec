<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

trait UploadedFileTestTrait
{
    abstract protected function stream($data): StreamInterface;

    abstract protected function uploadedFile($streamOrResource, $size, $error, $clientFileName = null, $clientMediaType = null): UploadedFileInterface;

    /**
     * @dataProvider invalid_file_upload_error_statuses
     * @expectedException InvalidArgumentException
     * @param $status
     */
    public function test_setting_invalid_error_status_raises_exception($status)
    {
        $this->uploadedFile(fopen('php://temp', 'wb+'), 0, $status);
    }

    public function test_setting_stream_returns_original_stream()
    {
        $resource = \fopen('php://temp', 'r');
        $stream = $this->stream($resource);

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $this->assertSame($stream, $upload->getStream());
    }

    public function test_setting_resource_returns_wrapped_stream()
    {
        $stream = fopen('php://temp', 'wb+');
        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);

        $uploadStream = $upload->getStream()->detach();
        $this->assertSame($stream, $uploadStream);
    }

    public function test_move_file_to_designated_path()
    {
        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);

        $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));
        $contents = file_get_contents($to);
        $this->assertSame($stream->__toString(), $contents);
    }

    /**
     * @dataProvider invalid_target_paths
     * @expectedException InvalidArgumentException
     * @param $path
     */
    public function test_moving_to_invalid_path_raises_exception($path)
    {
        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $upload->moveTo($path);
    }

    /**
     * @expectedException RuntimeException
     */
    public function test_move_cannot_be_called_more_than_once()
    {
        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));
        $upload->moveTo($to);
    }

    /**
     * @expectedException RuntimeException
     */
    public function test_cannot_retrieve_stream_after_move()
    {
        $stream = $this->stream(fopen('php://temp', 'wb+'));
        $stream->write('Foo bar!');

        $upload = $this->uploadedFile($stream, 0, UPLOAD_ERR_OK);
        $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));
        $upload->getStream();
    }

    public function test_move_to_creates_stream_only_path_provided()
    {
        $source = tempnam(sys_get_temp_dir(), 'source');
        $target = tempnam(sys_get_temp_dir(), 'source');

        file_put_contents($source, md5(rand(1, 10000) . time()));
        $uploadedFile = $this->uploadedFile($source, 100, UPLOAD_ERR_OK, basename(__FILE__), 'text/plain');

        $uploadedFile->moveTo($target);
        $this->assertSame(file_get_contents($source), file_get_contents($target));
    }

    /**
     * @dataProvider file_upload_error_codes
     * @expectedException RuntimeException
     * @param int $code
     */
    public function test_non_ok_error_code_raises_exception_on_get_stream($code)
    {
        $source = tempnam(sys_get_temp_dir(), 'source');

        $uploadedFile = $this->uploadedFile($source, 100, $code);
        $uploadedFile->getStream();
    }

    /**
     * @dataProvider file_upload_error_codes
     * @expectedException RuntimeException
     * @param int $code
     */
    public function test_non_ok_error_code_raises_exception_on_move_to($code)
    {
        $source = tempnam(sys_get_temp_dir(), 'source');

        $uploadedFile = $this->uploadedFile($source, 100, $code);
        $uploadedFile->moveTo('/tmp/foo');
    }

    /**
     * @dataProvider invalid_file_sizes
     * @expectedException InvalidArgumentException
     * @param $size
     */
    public function test_setting_invalid_file_size_raises_exception($size)
    {
        $this->uploadedFile(fopen('php://temp', 'wb+'), $size, UPLOAD_ERR_OK);
    }

    /**
     * @dataProvider invalid_file_names
     * @expectedException InvalidArgumentException
     * @param $fileName
     */
    public function test_invalid_client_file_names_raise_an_exception($fileName)
    {
        $this->uploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, $fileName);
    }

    /**
     * @dataProvider invalid_media_types
     * @expectedException InvalidArgumentException
     * @param $mediaType
     */
    public function test_invalid_client_media_type_raise_an_exception($mediaType)
    {
        $this->uploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'foobar.baz', $mediaType);
    }
}
