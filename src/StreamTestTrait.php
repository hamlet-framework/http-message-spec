<?php

namespace Hamlet\Http\Message\Spec\Traits;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

trait StreamTestTrait
{
    abstract protected function stream($handle): StreamInterface;

    protected function tempFileName(): string
    {
        try {
            return tempnam(sys_get_temp_dir(), 'psr-7') . '.' . md5(time() . random_bytes(12));
        } catch (Exception $e) {
            error_log($e->getMessage());
            return '';
        }
    }

    public function test_constructor_initializes_properties()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        Assert::assertTrue($stream->isReadable());
        Assert::assertTrue($stream->isWritable());
        Assert::assertTrue($stream->isSeekable());
        Assert::assertEquals('php://temp', $stream->getMetadata('uri'));
        Assert::assertIsArray($stream->getMetadata());
        Assert::assertEquals(4, $stream->getSize());
        Assert::assertFalse($stream->eof());

        $stream->close();
    }

    public function test_constructor_initializes_properties_with_rb_plus()
    {
        $handle = fopen('php://temp', 'rb+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        Assert::assertTrue($stream->isReadable());
        Assert::assertTrue($stream->isWritable());
        Assert::assertTrue($stream->isSeekable());
        Assert::assertEquals('php://temp', $stream->getMetadata('uri'));
        Assert::assertIsArray($stream->getMetadata());
        Assert::assertEquals(4, $stream->getSize());
        Assert::assertFalse($stream->eof());

        $stream->close();
    }

    public function test_stream_closes_handle_on_destruct()
    {
        $handle = fopen('php://temp', 'r');

        $stream = $this->stream($handle);
        unset($stream);
        Assert::assertFalse(is_resource($handle));
    }

    public function test_converts_to_string()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        Assert::assertEquals('data', (string)$stream);
        Assert::assertEquals('data', (string)$stream);

        $stream->close();
    }

    public function test_gets_contents()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        Assert::assertEquals('', $stream->getContents());

        $stream->seek(0);
        Assert::assertEquals('data', $stream->getContents());
        Assert::assertEquals('', $stream->getContents());

        $stream->close();
    }

    public function test_checks_eof()
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        Assert::assertSame(4, $stream->tell(), 'Stream cursor already at the end');
        Assert::assertFalse($stream->eof(), 'Stream still not eof');
        Assert::assertSame('', $stream->read(1), 'Need to read one more byte to reach eof');
        Assert::assertTrue($stream->eof());

        $stream->close();
    }

    public function test_get_size()
    {
        $size = filesize(__FILE__);
        $handle = fopen(__FILE__, 'r');

        $stream = $this->stream($handle);
        Assert::assertEquals($size, $stream->getSize());
        // Load from cache
        Assert::assertEquals($size, $stream->getSize());

        $stream->close();
    }

    public function test_ensures_size_is_consistent()
    {
        $handle = fopen('php://temp', 'w+');
        Assert::assertEquals(3, fwrite($handle, 'foo'));

        $stream = $this->stream($handle);
        Assert::assertEquals(3, $stream->getSize());
        Assert::assertEquals(4, $stream->write('test'));
        Assert::assertEquals(7, $stream->getSize());
        Assert::assertEquals(7, $stream->getSize());

        $stream->close();
    }

    public function test_provides_stream_position()
    {
        $handle = fopen('php://temp', 'w+');

        $stream = $this->stream($handle);
        Assert::assertEquals(0, $stream->tell());

        $stream->write('foo');
        Assert::assertEquals(3, $stream->tell());

        $stream->seek(1);
        Assert::assertEquals(1, $stream->tell());
        Assert::assertSame(ftell($handle), $stream->tell());

        $stream->close();
    }

    public function test_detach_stream_and_clear_properties()
    {
        $handle = fopen('php://temp', 'r');

        $stream = $this->stream($handle);
        Assert::assertSame($handle, $stream->detach());
        Assert::assertIsResource($handle);
        Assert::assertNull($stream->detach());
        $this->assert_stream_state_after_closed_or_detached($stream);

        $stream->close();
    }

    public function test_close_resource_and_clear_properties()
    {
        $handle = fopen('php://temp', 'r');

        $stream = $this->stream($handle);
        $stream->close();
        Assert::assertFalse(is_resource($handle));
        $this->assert_stream_state_after_closed_or_detached($stream);
    }

    private function assert_stream_state_after_closed_or_detached(StreamInterface $stream)
    {
        Assert::assertFalse($stream->isReadable());
        Assert::assertFalse($stream->isWritable());
        Assert::assertFalse($stream->isSeekable());
        Assert::assertNull($stream->getSize());
        Assert::assertSame([], $stream->getMetadata());
        Assert::assertNull($stream->getMetadata('foo'));
        $throws = function (callable $fn) {
            try {
                $fn();
            } catch (Exception $e) {
                return;
            }
            $this->fail('Exception should be thrown after the stream is detached.');
        };
        $throws(function () use ($stream) {
            $stream->read(10);
        });
        $throws(function () use ($stream) {
            $stream->write('bar');
        });
        $throws(function () use ($stream) {
            $stream->seek(10);
        });
        $throws(function () use ($stream) {
            $stream->tell();
        });
        $throws(function () use ($stream) {
            $stream->eof();
        });
        $throws(function () use ($stream) {
            $stream->getContents();
        });
        Assert::assertSame('', (string)$stream);
    }

    public function test_stream_reading_with_zero_length()
    {
        $handle = fopen('php://temp', 'r');

        $stream = $this->stream($handle);
        Assert::assertSame('', $stream->read(0));
        $stream->close();
    }

    public function test_stream_reading_with_negative_length()
    {
        $this->expectException(InvalidArgumentException::class);

        $handle = fopen('php://temp', 'r');
        $stream = $this->stream($handle);
        try {
            $stream->read(-1);
        } finally {
            $stream->close();
        }
    }

    public function test_can_detach_stream()
    {
        $handle = fopen('php://temp', 'w+');

        $stream = $this->stream($handle);
        $stream->write('foo');
        Assert::assertTrue($stream->isReadable());
        Assert::assertSame($handle, $stream->detach());

        $stream->detach();
        Assert::assertFalse($stream->isReadable());
        Assert::assertFalse($stream->isWritable());
        Assert::assertFalse($stream->isSeekable());

        $throws = function (callable $fn) use ($stream) {
            try {
                $fn($stream);
                $this->fail();
            } catch (Exception $e) {
            }
        };
        $throws(function (StreamInterface $stream) {
            $stream->read(10);
        });
        $throws(function (StreamInterface $stream) {
            $stream->write('bar');
        });
        $throws(function (StreamInterface $stream) {
            $stream->seek(10);
        });
        $throws(function (StreamInterface $stream) {
            $stream->tell();
        });
        $throws(function (StreamInterface $stream) {
            $stream->eof();
        });
        $throws(function (StreamInterface $stream) {
            $stream->getSize();
        });
        $throws(function (StreamInterface $stream) {
            $stream->getContents();
        });
        Assert::assertSame('', (string)$stream);
        $stream->close();
    }

    public function test_close_clear_properties()
    {
        $handle = fopen('php://temp', 'r+');

        $stream = $this->stream($handle);
        $stream->close();
        Assert::assertFalse($stream->isSeekable());
        Assert::assertFalse($stream->isReadable());
        Assert::assertFalse($stream->isWritable());
        Assert::assertNull($stream->getSize());
        Assert::assertEmpty($stream->getMetadata());
    }

    /**
     * @dataProvider non_readable_modes
     * @param string $mode
     */
    public function test_is_readable_returns_false_if_stream_is_not_readable(string $mode)
    {
        $name = $this->tempFileName();
        $handle = fopen($name, $mode);

        $stream = $this->stream($handle);
        Assert::assertFalse($stream->isReadable());

        $stream->close();
    }

    /**
     * @dataProvider non_writable_modes
     * @param string $mode
     */
    public function test_is_writable_returns_false_if_stream_is_not_writable(string $mode)
    {
        $handle = fopen('php://memory', $mode);

        $stream = $this->stream($handle);
        Assert::assertFalse($stream->isWritable());

        $stream->close();
    }

    public function test_to_string_retrieves_full_contents_of_stream()
    {
        $stream = $this->stream(fopen('php://memory', 'rw'));
        $message = 'foo bar';

        $stream->write($message);
        Assert::assertSame($message, (string)$stream);
    }

    public function test_detach_returns_resource()
    {
        $handle = fopen('php://memory', 'wb+');

        $stream = $this->stream($handle);
        Assert::assertSame($handle, $stream->detach());
    }

    /**
     * @dataProvider invalid_resources
     * @param mixed $handle
     */
    public function test_passing_invalid_stream_resource_to_constructor_raises_exception($handle)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->stream($handle);
    }

    public function test_string_serialization_empty_when_stream_not_readable()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');

        $handle = fopen($name, 'w');
        $stream = $this->stream($handle);
        Assert::assertSame('', (string)$stream);
    }

    public function test_close_unsets_resource()
    {
        $name = $this->tempFileName();
        $handle = fopen($name, 'wb+');

        $stream = $this->stream($handle);
        $stream->close();
        Assert::assertNull($stream->detach());
    }

    public function test_close_does_nothing_after_detach()
    {
        $name = $this->tempFileName();
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $detached = $stream->detach();
        $stream->close();
        Assert::assertTrue(is_resource($detached));
        Assert::assertSame($resource, $detached);
    }

    public function test_size_reports_null_when_no_resource_present()
    {
        $name = $this->tempFileName();
        $resource = fopen($name, 'w+');
        $stream = $this->stream($resource);
        $stream->write('here we go');

        $stream->detach();
        Assert::assertNull($stream->getSize());
    }

    public function test_tell_reports_current_position_in_resource()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        fseek($resource, 2);
        Assert::assertSame(2, $stream->tell());
    }

    public function test_tell_raises_exception_if_resource_is_detached()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        fseek($resource, 2);
        $stream->detach();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource');
        $stream->tell();
    }

    public function test_eof_reports_false_when_not_at_end_of_stream()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        fseek($resource, 2);
        Assert::assertFalse($stream->eof());
    }

    public function test_eof_reports_true_when_at_end_of_stream()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        while (!feof($resource)) {
            fread($resource, 4096);
        }
        Assert::assertTrue($stream->eof());
    }

    public function test_is_seekable_returns_true_for_readable_streams()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        Assert::assertTrue($stream->isSeekable());
    }

    public function test_is_seekable_returns_false_for_detached_streams()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        Assert::assertFalse($stream->isSeekable());
    }

    public function test_seek_advances_to_given_offset_of_stream()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        Assert::assertNull($stream->seek(2));
        Assert::assertSame(2, $stream->tell());
    }

    public function test_rewind_resets_to_start_of_stream()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        Assert::assertNull($stream->seek(2));
        $stream->rewind();
        Assert::assertSame(0, $stream->tell());
    }

    public function test_seek_raises_exception_when_stream_is_detached()
    {
        $this->expectException(RuntimeException::class);

        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        $stream->seek(2);
    }

    public function test_is_writable_returns_false_when_stream_is_detached()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        Assert::assertFalse($stream->isWritable());
    }

    public function test_is_writable_returns_true_for_writable_memory_stream()
    {
        $handle = fopen("php://temp", "r+b");
        $stream = $this->stream($handle);
        Assert::assertTrue($stream->isWritable());
    }

    public function test_write_raises_exception_when_stream_is_detached()
    {
        $this->expectException(RuntimeException::class);

        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        $stream->write('bar');
    }

    /**
     * @dataProvider non_writable_modes
     * @param string $mode
     */
    public function test_write_raises_exception_when_stream_is_not_writable(string $mode)
    {
        $this->expectException(RuntimeException::class);

        $handle = fopen('php://memory', $mode);
        $stream = $this->stream($handle);
        $stream->write('bar');
    }

    public function test_is_readable_returns_false_when_stream_is_detached()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        Assert::assertFalse($stream->isReadable());
    }

    public function test_read_raises_exception_when_stream_is_detached()
    {
        $this->expectException(RuntimeException::class);

        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'r');
        $stream = $this->stream($resource);
        $stream->detach();
        $stream->read(4096);
    }

    public function test_read_returns_empty_string_when_at_end_of_file()
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'r');
        $stream = $this->stream($resource);
        while (!feof($resource)) {
            fread($resource, 4096);
        }
        Assert::assertSame('', $stream->read(4096));
    }

    /**
     * @dataProvider non_readable_modes
     * @param string $mode
     */
    public function test_get_contents_raises_exception_if_stream_is_not_readable(string $mode)
    {
        $this->expectException(RuntimeException::class);

        $name = $this->tempFileName();
        if ($mode[0] != 'x') {
            file_put_contents($name, 'MODE: ' . $mode);
        }
        $resource = fopen($name, $mode);
        $stream = $this->stream($resource);
        $stream->getContents();
    }

    public function test_size_reports_null_for_php_input_streams()
    {
        $resource = fopen('php://input', 'r');
        $stream = $this->stream($resource);
        Assert::assertNull($stream->getSize());
    }

    public function test_get_size_returns_stream_size()
    {
        $resource = fopen(__FILE__, 'r');
        $stats = fstat($resource);
        $stream = $this->stream($resource);
        Assert::assertSame($stats['size'], $stream->getSize());
    }

    public function test_modes()
    {
        foreach ($this->all_modes() as list($mode, $readable, $writable)) {
            preg_match('/^(rw|.\+?)/', $mode, $matches);
            $prefix = $matches[1];
            switch ($prefix) {
                case 'r':
                    Assert::assertTrue($readable);
                    Assert::assertFalse($writable);
                    break;
                case 'w':
                case 'a':
                case 'x':
                case 'c':
                    Assert::assertFalse($readable);
                    Assert::assertTrue($writable);
                    break;
                case 'r+':
                case 'w+':
                case 'a+':
                case 'x+':
                case 'c+':
                case 'rw':
                    Assert::assertTrue($readable);
                    Assert::assertTrue($writable);
                    break;
            }
        }
    }
}
