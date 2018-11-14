<?php

namespace Hamlet\Http\Message\Spec\Traits;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

trait MessageTestTrait
{
    abstract protected function message(): MessageInterface;

    abstract protected function stream(): StreamInterface;

    /**
     * @dataProvider valid_protocol_versions
     * @param $version
     */
    public function test_accepts_valid_protocol_version($version)
    {
        $message = $this->message()->withProtocolVersion($version);
        Assert::assertEquals($version, $message->getProtocolVersion());
    }

    /**
     * @dataProvider invalid_protocol_versions
     * @expectedException InvalidArgumentException
     * @param mixed $version
     */
    public function test_with_invalid_protocol_version_raises_exception($version)
    {
        $this->message()->withProtocolVersion($version)->getProtocolVersion();
    }

    public function test_with_protocol_version_preserves_original_message()
    {
        $version = rand(1, 10) . '.' . rand(0, 9);
        $message = $this->message()->withProtocolVersion($version);
        $message->withProtocolVersion('9.0');
        Assert::assertEquals($version, $message->getProtocolVersion());
    }

    /**
     * @dataProvider valid_header_names
     * @param $name
     * @throws Exception
     */
    public function test_with_header_accepts_valid_header_names($name)
    {
        $value = base64_encode(random_bytes(12));
        $message = $this->message()->withHeader($name, $value);
        Assert::assertEquals($value, $message->getHeaderLine($name));
    }

    /**
     * @dataProvider valid_header_names
     * @param $name
     * @throws Exception
     */
    public function test_with_added_header_accepts_valid_header_names($name)
    {
        $value = base64_encode(random_bytes(12));
        $message = $this->message()->withAddedHeader($name, $value);
        Assert::assertEquals($value, $message->getHeaderLine($name));
    }

    /**
     * @dataProvider invalid_header_names
     * @expectedException InvalidArgumentException
     * @param $name
     * @throws Exception
     */
    public function test_invalid_header_name_raises_exception($name)
    {
        $value = base64_encode(random_bytes(12));
        $message = $this->message()->withAddedHeader($name, $value);
        $message->getHeaders();
    }

    /**
     * @dataProvider invalid_header_names
     * @expectedException InvalidArgumentException
     * @param $name
     * @throws Exception
     */
    public function test_invalid_added_header_name_raises_exception($name)
    {
        $value = base64_encode(random_bytes(12));
        $message = $this->message()->withAddedHeader($name, $value);
        $message->getHeaders();
    }

    /**
     * @dataProvider valid_header_values
     * @param $value
     */
    public function test_with_header_accept_valid_values($value)
    {
        $message = $this->message()->withHeader('header', $value);
        Assert::assertEquals($value, $message->getHeaderLine('header'));
    }

    /**
     * @dataProvider valid_header_values
     * @param $value
     */
    public function test_with_added_header_accepts_valid_values($value)
    {
        $message = $this->message()->withAddedHeader('header', $value);
        Assert::assertEquals($value, $message->getHeaderLine('header'));
    }

    /**
     * @dataProvider invalid_header_values
     * @expectedException InvalidArgumentException
     * @param mixed $value
     */
    public function test_with_header_rejects_invalid_values($value)
    {
        $message = $this->message()->withHeader('header', $value);
        $message->getHeaders();
    }

    /**
     * @dataProvider invalid_header_values
     * @expectedException InvalidArgumentException
     * @param mixed $value
     */
    public function test_with_added_header_rejects_invalid_values($value)
    {
        $message = $this->message()->withAddedHeader('header', $value);
        $message->getHeaders();
    }

    public function test_with_added_header_aggregates_headers_without_removing_duplicates()
    {
        $request = $this->message()
            ->withHeader('Zoo', 'a')
            ->withAddedHeader('zoo', ['b', 'c', 'a']);

        Assert::assertEquals(['Zoo' => ['a', 'b', 'c', 'a']], $request->getHeaders());
    }

    public function test_get_header_returns_empty_array_when_header_not_present()
    {
        Assert::assertSame([], $this->message()->getHeader('Accept'));
    }

    public function test_get_header_ignores_case()
    {
        $message = $this->message()->withHeader('LANGUAGE', 'ru-RU');
        Assert::assertEquals('ru-RU', $message->getHeaderLine('Language'));
    }

    public function test_with_header_accepts_list_of_values()
    {
        $request = $this->message()->withHeader('Foo', ['a', 'b', 'c']);
        Assert::assertEquals('a, b, c', $request->getHeaderLine('Foo'));
    }

    public function test_with_added_header_accepts_list_of_values()
    {
        $request = $this->message()
            ->withHeader('Foo', 'a')
            ->withAddedHeader('Foo', ['b', 'c']);

        Assert::assertEquals('a, b, c', $request->getHeaderLine('Foo'));
    }

    public function test_get_header_line_returns_empty_string_when_header_not_present()
    {
        Assert::assertSame('', $this->message()->getHeaderLine('Language'));
    }

    public function test_get_header_line_keeps_values_unescaped()
    {
        $message = $this->message()->withHeader('Zoo', ['elephant', 'monkey, rhino', 't-rex!']);
        Assert::assertSame('elephant, monkey, rhino, t-rex!', $message->getHeaderLine('zoo'));
    }

    public function test_host_header_is_added_first()
    {
        $request = $this->message()
            ->withHeader('Foo', 'Bar')
            ->withHeader('Host', 'foo.com');

        Assert::assertEquals([
            'Host' => ['foo.com'],
            'Foo'  => ['Bar']
        ], $request->getHeaders());
    }

    /**
     * @dataProvider host_header_variations
     * @param string $name
     * @throws Exception
     */
    public function test_host_header_name_gets_normalized(string $name)
    {
        $value = base64_encode(random_bytes(12));
        $headers = $this->message()->withHeader($name, $value)->getHeaders();

        Assert::assertArrayHasKey('Host', $headers);
        Assert::assertSame([$value], $headers['Host']);

        if ($name != 'Host') {
            Assert::assertArrayNotHasKey($name, $headers);
        }
    }

    public function test_host_header_not_duplicated()
    {
        $message = $this->message()
            ->withHeader('Host', 'example.com')
            ->withAddedHeader('Host', 'example.net');

        Assert::assertEquals('example.net', $message->getHeaderLine('Host'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_with_header_rejects_multiple_host_values()
    {
        $message = $this->message()->withHeader('Host', ['a.com', 'b.com']);
        $message->getHeaders();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_with_added_header_rejects_multiple_host_values()
    {
        $message = $this->message()->withAddedHeader('Host', ['a.com', 'b.com']);
        $message->getHeaders();
    }

    public function test_without_header_removes_header()
    {
        $message = $this->message()->withHeader('Language', 'ru');

        Assert::assertEquals('ru', $message->getHeaderLine('Language'));
        Assert::assertEquals('', $message->withoutHeader('Language')->getHeaderLine('Language'));
    }

    /**
     * @throws Exception
     */
    public function test_with_header_preserves_original_message()
    {
        $name  = 'Header' . md5(random_bytes(12));
        $value = base64_encode(random_bytes(12));

        $message = $this->message()->withHeader($name, $value);
        $message->withHeader($name, $value . '-replaced');

        Assert::assertEquals($value, $message->getHeaderLine($name));
    }

    /**
     * @throws Exception
     */
    public function test_with_added_header_preserves_original_message()
    {
        $name  = 'Header' . md5(random_bytes(12));
        $value = md5(random_bytes(12));

        $message = $this->message()->withHeader($name, $value);
        $message->withAddedHeader($name, $value . '-added');

        Assert::assertEquals($value, $message->getHeaderLine($name));
    }

    /**
     * @throws Exception
     */
    public function test_without_header_preserves_original_message()
    {
        $name  = 'Header' . md5(random_bytes(12));
        $value = base64_encode(random_bytes(12));

        $message = $this->message()->withHeader($name, $value);
        $message->withoutHeader($name);

        Assert::assertEquals($value, $message->getHeaderLine($name));
    }

    public function test_with_body_preserves_original_message()
    {
        $body = self::stream();
        $message = $this->message()->withBody($body);

        $message->withBody(self::stream());
        Assert::assertSame($body, $message->getBody());
    }

    public function test_empty_message_returns_body_stream()
    {
        Assert::assertInstanceOf(StreamInterface::class, $this->message()->getBody());
    }

    public function test_header_values_are_trimmed()
    {
        $message1 = $this->message()->withHeader('OWS', " \t \tFoo\t \t ");
        $message2 = $this->message()->withAddedHeader('OWS', " \t \tFoo\t \t ");;
        foreach ([$message1, $message2] as $message) {
            $this->assertSame(['OWS' => ['Foo']], $message->getHeaders());
            $this->assertSame('Foo', $message->getHeaderLine('OWS'));
            $this->assertSame(['Foo'], $message->getHeader('OWS'));
        }
    }

    /**
     * @dataProvider headers_with_injection_vectors
     * @expectException InvalidArgumentException
     * @param $name
     * @param $value
     */
    public function test_with_header_rejects_headers_with_crlf_vectors($name, $value)
    {
        $this->message()->withHeader($name, $value);
    }

    /**
     * @dataProvider headers_with_injection_vectors
     * @expectException InvalidArgumentException
     * @param $name
     * @param $value
     */
    public function test_with_added_header_rejects_headers_with_crlf_vectors($name, $value)
    {
        $this->message()->withAddedHeader($name, $value);
    }

    /**
     * @dataProvider invalid_body
     * @expectException InvalidArgumentException
     * @param $body
     */
    public function test_setting_invalid_body_raises_exception($body)
    {
        $message = $this->message()->withBody($body);
        $message->getBody();
    }
}
