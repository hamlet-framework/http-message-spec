<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use function RingCentral\Psr7\stream_for;

trait UploadedFileTestTrait
{
    protected function stream($data): StreamInterface
    {
        return stream_for($data);
    }

    /**
     * @dataProvider invalid_streams
     * @expectedException InvalidArgumentException
     */
    public function testRaisesExceptionOnInvalidStreamOrFile($streamOrFile)
    {
        new UploadedFile($streamOrFile, 0, UPLOAD_ERR_OK);
    }

    public function testValidSize()
    {
        $uploaded = new UploadedFile(fopen('php://temp', 'wb+'), 123, UPLOAD_ERR_OK);
        $this->assertSame(123, $uploaded->getSize());
    }

    /**
     * @dataProvider invalid_file_upload_error_statuses
     * @expectedException InvalidArgumentException
     */
    public function testRaisesExceptionOnInvalidErrorStatus($status)
    {
        new UploadedFile(fopen('php://temp', 'wb+'), 0, $status);
    }

    public function testValidClientFilename()
    {
        $file = new UploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'boo.txt');
        $this->assertSame('boo.txt', $file->getClientFilename());
    }

    public function testValidNullClientFilename()
    {
        $file = new UploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, null);
        $this->assertSame(null, $file->getClientFilename());
    }

    public function testValidClientMediaType()
    {
        $file = new UploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'foobar.baz', 'mediatype');
        $this->assertSame('mediatype', $file->getClientMediaType());
    }

    public function testGetStreamReturnsOriginalStreamObject()
    {
        $resource = \fopen('php://temp', 'r');
        $stream = $this->stream($resource);
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $this->assertSame($stream, $upload->getStream());
    }

    public function testGetStreamReturnsWrappedPhpStream()
    {
        $stream = fopen('php://temp', 'wb+');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $uploadStream = $upload->getStream()->detach();
        $this->assertSame($stream, $uploadStream);
    }

    public function testMovesFileToDesignatedPath()
    {
        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'diac');
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
    public function testMoveRaisesExceptionForInvalidPath($path)
    {
        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $upload->moveTo($path);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testMoveCannotBeCalledMoreThanOnce()
    {
        $resource = fopen('php://temp', 'wb+');
        $stream = $this->stream($resource);
        $stream->write('Foo bar!');

        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));
        $upload->moveTo($to);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCannotRetrieveStreamAfterMove()
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write('Foo bar!');
        $upload = new UploadedFile($stream, 0, UPLOAD_ERR_OK);
        $this->tmpFile = $to = tempnam(sys_get_temp_dir(), 'diac');
        $upload->moveTo($to);
        $this->assertTrue(file_exists($to));
        $upload->getStream();
    }

    public function nonOkErrorStatus()
    {
        return [
            'UPLOAD_ERR_INI_SIZE' => [UPLOAD_ERR_INI_SIZE],
            'UPLOAD_ERR_FORM_SIZE' => [UPLOAD_ERR_FORM_SIZE],
            'UPLOAD_ERR_PARTIAL' => [UPLOAD_ERR_PARTIAL],
            'UPLOAD_ERR_NO_FILE' => [UPLOAD_ERR_NO_FILE],
            'UPLOAD_ERR_NO_TMP_DIR' => [UPLOAD_ERR_NO_TMP_DIR],
            'UPLOAD_ERR_CANT_WRITE' => [UPLOAD_ERR_CANT_WRITE],
            'UPLOAD_ERR_EXTENSION' => [UPLOAD_ERR_EXTENSION],
        ];
    }

    /**
     * @dataProvider nonOkErrorStatus
     */
    public function testConstructorDoesNotRaiseExceptionForInvalidStreamWhenErrorStatusPresent($status)
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);
        $this->assertSame($status, $uploadedFile->getError());
    }

    /**
     * @dataProvider nonOkErrorStatus
     * @expectedException RuntimeException
     */
    public function testMoveToRaisesExceptionWhenErrorStatusPresent($status)
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);
        $uploadedFile->moveTo(__DIR__ . '/' . uniqid());
    }

    /**
     * @dataProvider nonOkErrorStatus
     * @expectedException RuntimeException
     */
    public function testGetStreamRaisesExceptionWhenErrorStatusPresent($status)
    {
        $uploadedFile = new UploadedFile('not ok', 0, $status);
        $uploadedFile->getStream();
    }

    public function testMoveToCreatesStreamIfOnlyAFilenameWasProvided()
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'DIA');
        $uploadedFile = new UploadedFile(__FILE__, 100, UPLOAD_ERR_OK, basename(__FILE__), 'text/plain');
        $uploadedFile->moveTo($this->tmpFile);
        $original = file_get_contents(__FILE__);
        $test = file_get_contents($this->tmpFile);
        $this->assertSame($original, $test);
    }

    public function errorConstantsAndMessages()
    {
        foreach (UploadedFile::ERROR_MESSAGES as $constant => $message) {
            if ($constant === UPLOAD_ERR_OK) {
                continue;
            }
            yield $constant => [$constant, $message];
        }
    }

    /**
     * @dataProvider errorConstantsAndMessages
     * @expectedException RuntimeException
     * @param int $constant Upload error constant
     * @param string $message Associated error message
     */
    public function testGetStreamRaisesExceptionWithAppropriateMessageWhenUploadErrorDetected($constant, $message)
    {
        $uploadedFile = new UploadedFile(__FILE__, 100, $constant);
        $uploadedFile->getStream();
    }

    /**
     * @dataProvider errorConstantsAndMessages
     * @expectedException RuntimeException
     * @param int $constant Upload error constant
     * @param string $message Associated error message
     */
    public function testMoveToRaisesExceptionWithAppropriateMessageWhenUploadErrorDetected($constant, $message)
    {
        $uploadedFile = new UploadedFile(__FILE__, 100, $constant);
        $uploadedFile->moveTo('/tmp/foo');
    }

    /**
     * @dataProvider invalid_file_sizes
     * @expectedException InvalidArgumentException
     * @param $size
     */
    public function testRaisesExceptionOnInvalidSize($size)
    {
        new UploadedFile(fopen('php://temp', 'wb+'), $size, UPLOAD_ERR_OK);
    }

    /**
     * @dataProvider invalid_file_names_and_media_types
     * @expectedException InvalidArgumentException
     * @param $fileName
     */
    public function testRaisesExceptionOnInvalidClientFilename($fileName)
    {
        new UploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, $fileName);
    }

    /**
     * @dataProvider invalid_file_names_and_media_types
     * @expectedException InvalidArgumentException
     * @param $mediaType
     */
    public function testRaisesExceptionOnInvalidClientMediaType($mediaType)
    {
        new UploadedFile(fopen('php://temp', 'wb+'), 0, UPLOAD_ERR_OK, 'foobar.baz', $mediaType);
    }


    public function testSuccessful()
    {
        $stream = \GuzzleHttp\Psr7\stream_for('Foo bar!');
        $upload = new UploadedFile($stream, $stream->getSize(), UPLOAD_ERR_OK, 'filename.txt', 'text/plain');
        $this->assertEquals($stream->getSize(), $upload->getSize());
        $this->assertEquals('filename.txt', $upload->getClientFilename());
        $this->assertEquals('text/plain', $upload->getClientMediaType());
        $this->cleanup[] = $to = tempnam(sys_get_temp_dir(), 'successful');
        $upload->moveTo($to);
        $this->assertFileExists($to);
        $this->assertEquals($stream->__toString(), file_get_contents($to));
    }
}
