<?php

namespace Hamlet\Http\Message\Spec\Traits;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

trait RequestTestTrait
{
    abstract protected function request(): RequestInterface;

    abstract protected function uri(string $value): UriInterface;

    public function test_request_target_defaults_to_slash()
    {
        $request1 = self::request();
        Assert::assertEquals('/', $request1->getRequestTarget());

        $request2 = self::request()->withUri(self::uri(''));
        Assert::assertEquals('/', $request2->getRequestTarget());

        $request3 = self::request()->withUri(self::uri('*'));
        Assert::assertEquals('*', $request3->getRequestTarget());

        $request4 = self::request()->withUri(self::uri('http://foo.com/bar baz/'));
        Assert::assertEquals('/bar%20baz/', $request4->getRequestTarget());

        $request5 = self::request()->withUri(self::uri('http://foo.com/?oops#2'));
        Assert::assertEquals('/?oops', $request5->getRequestTarget());
    }

    public function test_request_target_defaults_to_slash_when_uri_has_no_path_or_query()
    {
        $request = self::request()->withUri(self::uri('http://example.com'));
        Assert::assertSame('/', $request->getRequestTarget());
    }

    /**
     * @dataProvider valid_request_targets
     * @param string $requestTarget
     */
    public function test_with_request_target_accepts_valid_request_targets(string $requestTarget)
    {
        $request = self::request()->withRequestTarget($requestTarget);
        Assert::assertSame($requestTarget, $request->getRequestTarget());
    }

    /**
     * @dataProvider invalid_request_targets
     * @param mixed $target
     */
    public function test_with_request_target_rejects_invalid_request_targets($target)
    {
        $this->expectException(InvalidArgumentException::class);

        $request = self::request()->withRequestTarget($target);
        $request->getRequestTarget();
    }

    public function test_request_target_preserved_with_falsey_query()
    {
        $request = self::request()->withUri(self::uri('http://foo.com/baz?0'));
        Assert::assertEquals('/baz?0', $request->getRequestTarget());
    }

    /**
     * @throws Exception
     */
    public function test_with_request_target_preserves_original_request()
    {
        $target = base64_encode(random_bytes(12));
        $request = self::request()->withRequestTarget($target);
        $request->withRequestTarget($target . '-modified');

        Assert::assertEquals($target, $request->getRequestTarget());
    }

    /**
     * @dataProvider uris_with_request_targets
     * @param $uri
     * @param $target
     */
    public function test_request_target_is_read_from_uri($uri, $target)
    {
        $request = self::request()->withUri(self::uri($uri));
        Assert::assertEquals($target, $request->getRequestTarget());
    }

    /**
     * @throws Exception
     */
    public function test_with_new_uri_resets_request_target()
    {
        $uri = 'http://example.com/' . base64_encode(random_bytes(12));
        $request = self::request()->withUri(self::uri($uri));
        $target = $request->getRequestTarget();

        $request->withUri(self::uri('http://example.net'));
        Assert::assertEquals($target, $request->getRequestTarget());
    }

    public function test_method_is_get_by_default()
    {
        Assert::assertSame('GET', self::request()->getMethod());
    }

    public function test_with_method_preserves_case()
    {
        $request = self::request()->withMethod('get');
        Assert::assertEquals('get', $request->getMethod());
    }

    public function test_with_method_preserves_original_request()
    {
        $method = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        $request = self::request()->withMethod($method);

        $request->withMethod('POST');
        Assert::assertEquals($method, $request->getMethod());
    }

    /**
     * @dataProvider valid_request_methods
     * @param string $method
     */
    public function test_with_method_accepts_valid_method_names(string $method)
    {
        $request = self::request()->withMethod($method);
        Assert::assertSame($method, $request->getMethod());
    }

    /**
     * @dataProvider invalid_request_methods
     * @param $method
     */
    public function test_with_method_rejects_invalid_method_names($method)
    {
        $this->expectException(InvalidArgumentException::class);

        $request = self::request()->withMethod($method);
        $request->getMethod();
    }

    public function test_get_host_headers_gets_host_from_uri()
    {
        $request = self::request()->withUri(self::uri('http://example.com'));

        Assert::assertArrayHasKey('Host', $request->getHeaders());
        Assert::assertContains('example.com', $request->getHeader('Host'));
    }

    public function test_get_host_headers_gets_host_from_uri_ignoring_standard_port()
    {
        $request = self::request()->withUri(self::uri('http://example.com:80'));

        Assert::assertArrayHasKey('Host', $request->getHeaders());
        Assert::assertContains('example.com', $request->getHeader('Host'));
    }

    public function test_get_host_headers_gets_host_and_port_from_uri()
    {
        $request = self::request()->withUri(self::uri('http://foo.com:8124/bar'));

        Assert::assertArrayHasKey('Host', $request->getHeaders());
        Assert::assertEquals('foo.com:8124', $request->getHeaderLine('host'));
    }

    public function test_with_uri_overrides_host_header()
    {
        $request1 = self::request()->withUri(self::uri('http://foo.com/baz?bar=bam'));
        Assert::assertEquals('foo.com', $request1->getHeaderLine('host'));

        $request2 = $request1->withUri(self::uri('http://www.baz.com/bar'));
        Assert::assertEquals('www.baz.com', $request2->getHeaderLine('host'));
    }

    /**
     * @throws Exception
     */
    public function test_with_uri_preserved_host_if_required()
    {
        $host = md5(random_bytes(12)) . '.com';
        $request = self::request()->withHeader('Host', $host);

        Assert::assertEquals($host, $request->getHeaderLine('host'));

        $request = $request->withUri(self::uri('http://www.foo.com/bar'), true);
        Assert::assertEquals($host, $request->getHeaderLine('host'));
    }

    public function test_with_uri_replaces_host_header()
    {
        $request1 = self::request()->withUri(self::uri('http://foo.com:8124/bar'));
        Assert::assertEquals('foo.com:8124', $request1->getHeaderLine('host'));

        $request2 = $request1->withUri(self::uri('http://foo.com:8125/bar'));
        Assert::assertEquals('foo.com:8125', $request2->getHeaderLine('host'));
    }

    public function test_get_headers_contains_host_header_if_uri_with_host_is_deleted()
    {
        $request1 = self::request()->withUri(self::uri('http://www.example.com'));
        Assert::assertEquals('www.example.com', $request1->getHeaderLine('host'));

        $request2 = $request1->withoutHeader('host');
        Assert::assertArrayHasKey('Host', $request2->getHeaders());
        Assert::assertContains('www.example.com', $request2->getHeaderLine('Host'));
    }

    public function test_get_headers_contains_no_host_header_if_no_uri_present()
    {
        $request = self::request();
        Assert::assertFalse($request->hasHeader('host'));
    }

    public function test_get_headers_contains_no_host_header_if_uri_without_host()
    {
        $request = self::request()->withUri(self::uri('/test?a'));
        Assert::assertFalse($request->hasHeader('host'));
    }

    public function test_get_host_header_returns_uri_host_when_present()
    {
        $request = self::request()->withUri(self::uri('http://www.example.com'));
        Assert::assertSame(['www.example.com'], $request->getHeader('host'));
    }

    public function test_host_header_not_set_from_uri_if_host_header_specified_afterwards()
    {
        $request = self::request()
            ->withUri(self::uri('http://www.example.com'))
            ->withHeader('Host', 'www.test.com');

        Assert::assertSame('www.test.com', $request->getHeaderLine('host'));
    }

    public function test_host_header_updates_from_uri_when_not_preserving_host()
    {
        $request = self::request()->withAddedHeader('Host', 'example.com');
        $uri = self::uri('')
            ->withHost('www.example.com')
            ->withPort(10081);

        Assert::assertSame('www.example.com:10081', $request->withUri($uri)->getHeaderLine('Host'));
    }
}
