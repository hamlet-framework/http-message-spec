<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

trait MessageTestTrait
{
    abstract protected function message(): MessageInterface;

    abstract protected function stream(): StreamInterface;

    #[DataProvider('valid_protocol_versions')] public function test_accepts_valid_protocol_version(mixed $version): void
    {
        $message = $this->message()->withProtocolVersion($version);
        $this->assertEquals($version, $message->getProtocolVersion());
    }

    #[DataProvider('invalid_protocol_versions')] public function test_with_invalid_protocol_version_raises_exception(mixed $version): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->message()->withProtocolVersion($version)->getProtocolVersion();
    }

    public function test_with_protocol_version_preserves_original_message(): void
    {
        $version = rand(1, 10) . '.' . rand(0, 9);
        $message = $this->message()->withProtocolVersion($version);
        $message->withProtocolVersion('9.0');
        $this->assertEquals($version, $message->getProtocolVersion());
    }

    #[DataProvider('valid_header_names')] public function test_with_header_accepts_valid_header_names(mixed $name): void
    {
        $value = base64_encode(random_bytes(12));
        $message = $this->message()->withHeader($name, $value);
        $this->assertEquals($value, $message->getHeaderLine($name));
    }

    #[DataProvider('valid_header_names')] public function test_with_added_header_accepts_valid_header_names(mixed $name): void
    {
        $value = base64_encode(random_bytes(12));
        $message = $this->message()->withAddedHeader($name, $value);
        $this->assertEquals($value, $message->getHeaderLine($name));
    }

    #[DataProvider('invalid_header_names')] public function test_invalid_header_name_raises_exception(mixed $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $value = base64_encode(random_bytes(12));
        $message = $this->message()->withAddedHeader($name, $value);
        $message->getHeaders();
    }

    #[DataProvider('invalid_header_names')] public function test_invalid_added_header_name_raises_exception(mixed $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $value = base64_encode(random_bytes(12));
        $message = $this->message()->withAddedHeader($name, $value);
        $message->getHeaders();
    }

    #[DataProvider('valid_header_values')] public function test_with_header_accept_valid_values(mixed $value): void
    {
        $message = $this->message()->withHeader('header', $value);
        $this->assertEquals($value, $message->getHeaderLine('header'));
    }

    #[DataProvider('valid_header_values')] public function test_with_added_header_accepts_valid_values(mixed $value): void
    {
        $message = $this->message()->withAddedHeader('header', $value);
        $this->assertEquals($value, $message->getHeaderLine('header'));
    }

    #[DataProvider('invalid_header_values')] public function test_with_header_rejects_invalid_values(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $message = $this->message()->withHeader('header', $value);
        $message->getHeaders();
    }

    #[DataProvider('invalid_header_values')] public function test_with_added_header_rejects_invalid_values(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $message = $this->message()->withAddedHeader('header', $value);
        $message->getHeaders();
    }

    public function test_with_added_header_aggregates_headers_without_removing_duplicates(): void
    {
        $request = $this->message()
            ->withHeader('Zoo', 'a')
            ->withAddedHeader('zoo', ['b', 'c', 'a']);

        $this->assertEquals(['Zoo' => ['a', 'b', 'c', 'a']], $request->getHeaders());
    }

    public function test_get_header_returns_empty_array_when_header_not_present(): void
    {
        $this->assertSame([], $this->message()->getHeader('Accept'));
    }

    public function test_get_header_ignores_case(): void
    {
        $message = $this->message()->withHeader('LANGUAGE', 'ru-RU');
        $this->assertEquals('ru-RU', $message->getHeaderLine('Language'));
    }

    public function test_with_header_accepts_list_of_values(): void
    {
        $request = $this->message()->withHeader('Foo', ['a', 'b', 'c']);
        $this->assertEquals('a, b, c', $request->getHeaderLine('Foo'));
    }

    public function test_with_added_header_accepts_list_of_values(): void
    {
        $request = $this->message()
            ->withHeader('Foo', 'a')
            ->withAddedHeader('Foo', ['b', 'c']);

        $this->assertEquals('a, b, c', $request->getHeaderLine('Foo'));
    }

    public function test_get_header_line_returns_empty_string_when_header_not_present(): void
    {
        $this->assertSame('', $this->message()->getHeaderLine('Language'));
    }

    public function test_get_header_line_keeps_values_unescaped(): void
    {
        $message = $this->message()->withHeader('Zoo', ['elephant', 'monkey, rhino', 't-rex!']);
        $this->assertSame('elephant, monkey, rhino, t-rex!', $message->getHeaderLine('zoo'));
    }

    public function test_host_header_is_added_first(): void
    {
        $request = $this->message()
            ->withHeader('Foo', 'Bar')
            ->withHeader('Host', 'foo.com');

        $this->assertEquals([
            'Host' => ['foo.com'],
            'Foo'  => ['Bar']
        ], $request->getHeaders());
    }

    #[DataProvider('host_header_variations')] public function test_host_header_name_gets_normalized(string $name): void
    {
        $value = md5(random_bytes(12)) . '.com';
        $headers = $this->message()->withHeader($name, $value)->getHeaders();

        $this->assertArrayHasKey('Host', $headers);
        $this->assertSame([$value], $headers['Host']);

        if ($name != 'Host') {
            $this->assertArrayNotHasKey($name, $headers);
        }
    }

    public function test_host_header_not_duplicated(): void
    {
        $message = $this->message()
            ->withHeader('Host', 'example.com')
            ->withAddedHeader('Host', 'example.net');

        $this->assertEquals('example.net', $message->getHeaderLine('Host'));
    }

    public function test_with_header_rejects_multiple_host_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $message = $this->message()->withHeader('Host', ['a.com', 'b.com']);
        $message->getHeaders();
    }

    public function test_with_added_header_rejects_multiple_host_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $message = $this->message()->withAddedHeader('Host', ['a.com', 'b.com']);
        $message->getHeaders();
    }

    public function test_without_header_removes_header(): void
    {
        $message = $this->message()->withHeader('Language', 'ru');

        $this->assertEquals('ru', $message->getHeaderLine('Language'));
        $this->assertEquals('', $message->withoutHeader('Language')->getHeaderLine('Language'));
    }

    public function test_with_header_preserves_original_message(): void
    {
        $name  = 'Header' . md5(random_bytes(12));
        $value = base64_encode(random_bytes(12));

        $message = $this->message()->withHeader($name, $value);
        $message->withHeader($name, $value . '-replaced');

        $this->assertEquals($value, $message->getHeaderLine($name));
    }

    public function test_with_added_header_preserves_original_message(): void
    {
        $name  = 'Header' . md5(random_bytes(12));
        $value = md5(random_bytes(12));

        $message = $this->message()->withHeader($name, $value);
        $message->withAddedHeader($name, $value . '-added');

        $this->assertEquals($value, $message->getHeaderLine($name));
    }

    public function test_without_header_preserves_original_message(): void
    {
        $name  = 'Header' . md5(random_bytes(12));
        $value = base64_encode(random_bytes(12));

        $message = $this->message()->withHeader($name, $value);
        $message->withoutHeader($name);

        $this->assertEquals($value, $message->getHeaderLine($name));
    }

    public function test_with_body_preserves_original_message(): void
    {
        $body = $this->stream();
        $message = $this->message()->withBody($body);

        $message->withBody($this->stream());
        $this->assertSame($body, $message->getBody());
    }

    public function test_empty_message_returns_body_stream(): void
    {
        $this->assertInstanceOf(StreamInterface::class, $this->message()->getBody());
    }

    public function test_header_values_are_trimmed(): void
    {
        $message1 = $this->message()->withHeader('OWS', " \t \tFoo\t \t ");
        $message2 = $this->message()->withAddedHeader('OWS', " \t \tFoo\t \t ");
        foreach ([$message1, $message2] as $message) {
            $this->assertSame(['OWS' => ['Foo']], $message->getHeaders());
            $this->assertSame('Foo', $message->getHeaderLine('OWS'));
            $this->assertSame(['Foo'], $message->getHeader('OWS'));
        }
    }

    #[DataProvider('headers_with_injection_vectors')] public function test_with_header_rejects_headers_with_crlf_vectors(mixed $name, mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $message = $this->message()->withHeader($name, $value);
        $message->getHeaders();
    }

    #[DataProvider('headers_with_injection_vectors')] public function test_with_added_header_rejects_headers_with_crlf_vectors(mixed $name, mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $message = $this->message()->withAddedHeader($name, $value);
        $message->getHeaders();
    }
}
