<?php

namespace Hamlet\Http\Message\Spec\Traits;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_constructor_initializes_properties(): void
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertIsArray($stream->getMetadata());
        $this->assertEquals(4, $stream->getSize());
        $this->assertFalse($stream->eof());

        $stream->close();
    }

    public function test_constructor_initializes_properties_with_rb_plus(): void
    {
        $handle = fopen('php://temp', 'rb+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertIsArray($stream->getMetadata());
        $this->assertEquals(4, $stream->getSize());
        $this->assertFalse($stream->eof());

        $stream->close();
    }

    public function test_stream_closes_handle_on_destruct(): void
    {
        $handle = fopen('php://temp', 'r');

        $stream = $this->stream($handle);
        unset($stream);
        $this->assertFalse(is_resource($handle));
    }

    public function test_converts_to_string(): void
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        $this->assertEquals('data', (string)$stream);
        $this->assertEquals('data', (string)$stream);

        $stream->close();
    }

    public function test_gets_contents(): void
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        $this->assertEquals('', $stream->getContents());

        $stream->seek(0);
        $this->assertEquals('data', $stream->getContents());
        $this->assertEquals('', $stream->getContents());

        $stream->close();
    }

    public function test_checks_eof(): void
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, 'data');

        $stream = $this->stream($handle);
        $this->assertSame(4, $stream->tell(), 'Stream cursor already at the end');
        $this->assertFalse($stream->eof(), 'Stream still not eof');
        $this->assertSame('', $stream->read(1), 'Need to read one more byte to reach eof');
        $this->assertTrue($stream->eof());

        $stream->close();
    }

    public function test_get_size(): void
    {
        $size = filesize(__FILE__);
        $handle = fopen(__FILE__, 'r');

        $stream = $this->stream($handle);
        $this->assertEquals($size, $stream->getSize());
        // Load from cache
        $this->assertEquals($size, $stream->getSize());

        $stream->close();
    }

    public function test_ensures_size_is_consistent(): void
    {
        $handle = fopen('php://temp', 'w+');
        $this->assertEquals(3, fwrite($handle, 'foo'));

        $stream = $this->stream($handle);
        $this->assertEquals(3, $stream->getSize());
        $this->assertEquals(4, $stream->write('test'));
        $this->assertEquals(7, $stream->getSize());
        $this->assertEquals(7, $stream->getSize());

        $stream->close();
    }

    public function test_provides_stream_position(): void
    {
        $handle = fopen('php://temp', 'w+');

        $stream = $this->stream($handle);
        $this->assertEquals(0, $stream->tell());

        $stream->write('foo');
        $this->assertEquals(3, $stream->tell());

        $stream->seek(1);
        $this->assertEquals(1, $stream->tell());
        $this->assertSame(ftell($handle), $stream->tell());

        $stream->close();
    }

    public function test_detach_stream_and_clear_properties(): void
    {
        $handle = fopen('php://temp', 'r');

        $stream = $this->stream($handle);
        $this->assertSame($handle, $stream->detach());
        $this->assertIsResource($handle);
        $this->assertNull($stream->detach());
        $this->assert_stream_state_after_closed_or_detached($stream);

        $stream->close();
    }

    public function test_close_resource_and_clear_properties(): void
    {
        $handle = fopen('php://temp', 'r');

        $stream = $this->stream($handle);
        $stream->close();
        $this->assertFalse(is_resource($handle));
        $this->assert_stream_state_after_closed_or_detached($stream);
    }

    private function assert_stream_state_after_closed_or_detached(StreamInterface $stream): void
    {
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
        $this->assertNull($stream->getSize());
        $this->assertSame([], $stream->getMetadata());
        $this->assertNull($stream->getMetadata('foo'));
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
        $this->assertSame('', (string)$stream);
    }

    public function test_stream_reading_with_zero_length(): void
    {
        $handle = fopen('php://temp', 'r');

        $stream = $this->stream($handle);
        $this->assertSame('', $stream->read(0));
        $stream->close();
    }

    public function test_stream_reading_with_negative_length(): void
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

    public function test_can_detach_stream(): void
    {
        $handle = fopen('php://temp', 'w+');

        $stream = $this->stream($handle);
        $stream->write('foo');
        $this->assertTrue($stream->isReadable());
        $this->assertSame($handle, $stream->detach());

        $stream->detach();
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());

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
        $this->assertSame('', (string)$stream);
        $stream->close();
    }

    public function test_close_clear_properties(): void
    {
        $handle = fopen('php://temp', 'r+');

        $stream = $this->stream($handle);
        $stream->close();
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($stream->getSize());
        $this->assertEmpty($stream->getMetadata());
    }

    #[DataProvider('non_readable_modes')] public function test_is_readable_returns_false_if_stream_is_not_readable(string $mode): void
    {
        $name = $this->tempFileName();
        $handle = fopen($name, $mode);

        $stream = $this->stream($handle);
        $this->assertFalse($stream->isReadable());

        $stream->close();
    }

    #[DataProvider('non_writable_modes')] public function test_is_writable_returns_false_if_stream_is_not_writable(string $mode): void
    {
        $handle = fopen('php://memory', $mode);

        $stream = $this->stream($handle);
        $this->assertFalse($stream->isWritable());

        $stream->close();
    }

    public function test_to_string_retrieves_full_contents_of_stream(): void
    {
        $stream = $this->stream(fopen('php://memory', 'rw'));
        $message = 'foo bar';

        $stream->write($message);
        $this->assertSame($message, (string)$stream);
    }

    public function test_detach_returns_resource(): void
    {
        $handle = fopen('php://memory', 'wb+');

        $stream = $this->stream($handle);
        $this->assertSame($handle, $stream->detach());
    }

    #[DataProvider('invalid_resources')] public function test_passing_invalid_stream_resource_to_constructor_raises_exception(mixed $handle): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->stream($handle);
    }

    public function test_string_serialization_empty_when_stream_not_readable(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');

        $handle = fopen($name, 'w');
        $stream = $this->stream($handle);
        $this->assertSame('', (string)$stream);
    }

    public function test_close_unsets_resource(): void
    {
        $name = $this->tempFileName();
        $handle = fopen($name, 'wb+');

        $stream = $this->stream($handle);
        $stream->close();
        $this->assertNull($stream->detach());
    }

    public function test_close_does_nothing_after_detach(): void
    {
        $name = $this->tempFileName();
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $detached = $stream->detach();
        $stream->close();
        $this->assertTrue(is_resource($detached));
        $this->assertSame($resource, $detached);
    }

    public function test_size_reports_null_when_no_resource_present(): void
    {
        $name = $this->tempFileName();
        $resource = fopen($name, 'w+');
        $stream = $this->stream($resource);
        $stream->write('here we go');

        $stream->detach();
        $this->assertNull($stream->getSize());
    }

    public function test_tell_reports_current_position_in_resource(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        fseek($resource, 2);
        $this->assertSame(2, $stream->tell());
    }

    public function test_tell_raises_exception_if_resource_is_detached(): void
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

    public function test_eof_reports_false_when_not_at_end_of_stream(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        fseek($resource, 2);
        $this->assertFalse($stream->eof());
    }

    public function test_eof_reports_true_when_at_end_of_stream(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        while (!feof($resource)) {
            fread($resource, 4096);
        }
        $this->assertTrue($stream->eof());
    }

    public function test_is_seekable_returns_true_for_readable_streams(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $this->assertTrue($stream->isSeekable());
    }

    public function test_is_seekable_returns_false_for_detached_streams(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isSeekable());
    }

    public function test_seek_advances_to_given_offset_of_stream(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $this->assertNull($stream->seek(2));
        $this->assertSame(2, $stream->tell());
    }

    public function test_rewind_resets_to_start_of_stream(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $this->assertNull($stream->seek(2));
        $stream->rewind();
        $this->assertSame(0, $stream->tell());
    }

    public function test_seek_raises_exception_when_stream_is_detached(): void
    {
        $this->expectException(RuntimeException::class);

        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        $stream->seek(2);
    }

    public function test_is_writable_returns_false_when_stream_is_detached(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isWritable());
    }

    public function test_is_writable_returns_true_for_writable_memory_stream(): void
    {
        $handle = fopen("php://temp", "r+b");
        $stream = $this->stream($handle);
        $this->assertTrue($stream->isWritable());
    }

    public function test_write_raises_exception_when_stream_is_detached(): void
    {
        $this->expectException(RuntimeException::class);

        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        $stream->write('bar');
    }

    #[DataProvider('non_writable_modes')] public function test_write_raises_exception_when_stream_is_not_writable(string $mode): void
    {
        $this->expectException(RuntimeException::class);

        $handle = fopen('php://memory', $mode);
        $stream = $this->stream($handle);
        $stream->write('bar');
    }

    public function test_is_readable_returns_false_when_stream_is_detached(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'wb+');
        $stream = $this->stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isReadable());
    }

    public function test_read_raises_exception_when_stream_is_detached(): void
    {
        $this->expectException(RuntimeException::class);

        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'r');
        $stream = $this->stream($resource);
        $stream->detach();
        $stream->read(4096);
    }

    public function test_read_returns_empty_string_when_at_end_of_file(): void
    {
        $name = $this->tempFileName();
        file_put_contents($name, 'FOO BAR');
        $resource = fopen($name, 'r');
        $stream = $this->stream($resource);
        while (!feof($resource)) {
            fread($resource, 4096);
        }
        $this->assertSame('', $stream->read(4096));
    }

    #[DataProvider('non_readable_modes')] public function test_get_contents_raises_exception_if_stream_is_not_readable(string $mode): void
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

    public function test_size_reports_null_for_php_input_streams(): void
    {
        $resource = fopen('php://input', 'r');
        $stream = $this->stream($resource);
        $this->assertNull($stream->getSize());
    }

    public function test_get_size_returns_stream_size(): void
    {
        $resource = fopen(__FILE__, 'r');
        $stats = fstat($resource);
        $stream = $this->stream($resource);
        $this->assertSame($stats['size'], $stream->getSize());
    }

    public function test_modes(): void
    {
        foreach ($this->all_modes() as list($mode, $readable, $writable)) {
            preg_match('/^(rw|.\+?)/', $mode, $matches);
            $prefix = $matches[1];
            switch ($prefix) {
                case 'r':
                    $this->assertTrue($readable);
                    $this->assertFalse($writable);
                    break;
                case 'w':
                case 'a':
                case 'x':
                case 'c':
                    $this->assertFalse($readable);
                    $this->assertTrue($writable);
                    break;
                case 'r+':
                case 'w+':
                case 'a+':
                case 'x+':
                case 'c+':
                case 'rw':
                    $this->assertTrue($readable);
                    $this->assertTrue($writable);
                    break;
            }
        }
    }
}
