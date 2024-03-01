<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

trait RequestTestTrait
{
    abstract protected function request(): RequestInterface;

    abstract protected function uri(string $value): UriInterface;

    public function test_request_target_defaults_to_slash(): void
    {
        $request1 = $this->request();
        $this->assertEquals('/', $request1->getRequestTarget());

        $request2 = $this->request()->withUri($this->uri(''));
        $this->assertEquals('/', $request2->getRequestTarget());

        $request3 = $this->request()->withUri($this->uri('*'));
        $this->assertEquals('*', $request3->getRequestTarget());

        $request4 = $this->request()->withUri($this->uri('http://foo.com/bar baz/'));
        $this->assertEquals('/bar%20baz/', $request4->getRequestTarget());

        $request5 = $this->request()->withUri($this->uri('http://foo.com/?oops#2'));
        $this->assertEquals('/?oops', $request5->getRequestTarget());
    }

    public function test_request_target_defaults_to_slash_when_uri_has_no_path_or_query(): void
    {
        $request = $this->request()->withUri($this->uri('http://example.com'));
        $this->assertSame('/', $request->getRequestTarget());
    }

    #[DataProvider('valid_request_targets')] public function test_with_request_target_accepts_valid_request_targets(string $requestTarget): void
    {
        $request = $this->request()->withRequestTarget($requestTarget);
        $this->assertSame($requestTarget, $request->getRequestTarget());
    }

    #[DataProvider('invalid_request_targets')] public function test_with_request_target_rejects_invalid_request_targets(mixed $target): void
    {
        $this->expectException(InvalidArgumentException::class);

        $request = $this->request()->withRequestTarget($target);
        $request->getRequestTarget();
    }

    public function test_request_target_preserved_with_falsey_query(): void
    {
        $request = $this->request()->withUri($this->uri('http://foo.com/baz?0'));
        $this->assertEquals('/baz?0', $request->getRequestTarget());
    }

    public function test_with_request_target_preserves_original_request(): void
    {
        $target = base64_encode(random_bytes(12));
        $request = $this->request()->withRequestTarget($target);
        $request->withRequestTarget($target . '-modified');

        $this->assertEquals($target, $request->getRequestTarget());
    }

    #[DataProvider('uris_with_request_targets')] public function test_request_target_is_read_from_uri(mixed $uri, mixed $target): void
    {
        $request = $this->request()->withUri($this->uri($uri));
        $this->assertEquals($target, $request->getRequestTarget());
    }

    public function test_with_new_uri_resets_request_target(): void
    {
        $uri = 'http://example.com/' . base64_encode(random_bytes(12));
        $request = $this->request()->withUri($this->uri($uri));
        $target = $request->getRequestTarget();

        $request->withUri($this->uri('http://example.net'));
        $this->assertEquals($target, $request->getRequestTarget());
    }

    public function test_method_is_get_by_default(): void
    {
        $this->assertSame('GET', $this->request()->getMethod());
    }

    public function test_with_method_preserves_case(): void
    {
        $request = $this->request()->withMethod('get');
        $this->assertEquals('get', $request->getMethod());
    }

    public function test_with_method_preserves_original_request(): void
    {
        $method = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        $request = $this->request()->withMethod($method);

        $request->withMethod('POST');
        $this->assertEquals($method, $request->getMethod());
    }

    #[DataProvider('valid_request_methods')] public function test_with_method_accepts_valid_method_names(string $method): void
    {
        $request = $this->request()->withMethod($method);
        $this->assertSame($method, $request->getMethod());
    }

    #[DataProvider('invalid_request_methods')] public function test_with_method_rejects_invalid_method_names(mixed $method): void
    {
        $this->expectException(InvalidArgumentException::class);

        $request = $this->request()->withMethod($method);
        $request->getMethod();
    }

    public function test_get_host_headers_gets_host_from_uri(): void
    {
        $request = $this->request()->withUri($this->uri('http://example.com'));

        $this->assertArrayHasKey('Host', $request->getHeaders());
        $this->assertContains('example.com', $request->getHeader('Host'));
    }

    public function test_get_host_headers_gets_host_from_uri_ignoring_standard_port(): void
    {
        $request = $this->request()->withUri($this->uri('http://example.com:80'));

        $this->assertArrayHasKey('Host', $request->getHeaders());
        $this->assertContains('example.com', $request->getHeader('Host'));
    }

    public function test_get_host_headers_gets_host_and_port_from_uri(): void
    {
        $request = $this->request()->withUri($this->uri('http://foo.com:8124/bar'));

        $this->assertArrayHasKey('Host', $request->getHeaders());
        $this->assertEquals('foo.com:8124', $request->getHeaderLine('host'));
    }

    public function test_with_uri_overrides_host_header(): void
    {
        $request1 = $this->request()->withUri($this->uri('http://foo.com/baz?bar=bam'));
        $this->assertEquals('foo.com', $request1->getHeaderLine('host'));

        $request2 = $request1->withUri($this->uri('http://www.baz.com/bar'));
        $this->assertEquals('www.baz.com', $request2->getHeaderLine('host'));
    }

    public function test_with_uri_preserved_host_if_required(): void
    {
        $host = md5(random_bytes(12)) . '.com';
        $request = $this->request()->withHeader('Host', $host);

        $this->assertEquals($host, $request->getHeaderLine('host'));

        $request = $request->withUri($this->uri('http://www.foo.com/bar'), true);
        $this->assertEquals($host, $request->getHeaderLine('host'));
    }

    public function test_with_uri_replaces_host_header(): void
    {
        $request1 = $this->request()->withUri($this->uri('http://foo.com:8124/bar'));
        $this->assertEquals('foo.com:8124', $request1->getHeaderLine('host'));

        $request2 = $request1->withUri($this->uri('http://foo.com:8125/bar'));
        $this->assertEquals('foo.com:8125', $request2->getHeaderLine('host'));
    }

    public function test_get_headers_contains_host_header_if_uri_with_host_is_deleted(): void
    {
        $request1 = $this->request()->withUri($this->uri('http://www.example.com'));
        $this->assertEquals('www.example.com', $request1->getHeaderLine('host'));

        $request2 = $request1->withoutHeader('host');
        $this->assertArrayHasKey('Host', $request2->getHeaders());
        $this->assertStringContainsString('www.example.com', $request2->getHeaderLine('Host'));
    }

    public function test_get_headers_contains_no_host_header_if_no_uri_present(): void
    {
        $request = $this->request();
        $this->assertFalse($request->hasHeader('host'));
    }

    public function test_get_headers_contains_no_host_header_if_uri_without_host(): void
    {
        $request = $this->request()->withUri($this->uri('/test?a'));
        $this->assertFalse($request->hasHeader('host'));
    }

    public function test_get_host_header_returns_uri_host_when_present(): void
    {
        $request = $this->request()->withUri($this->uri('http://www.example.com'));
        $this->assertSame(['www.example.com'], $request->getHeader('host'));
    }

    public function test_host_header_not_set_from_uri_if_host_header_specified_afterwards(): void
    {
        $request = $this->request()
            ->withUri($this->uri('http://www.example.com'))
            ->withHeader('Host', 'www.test.com');

        $this->assertSame('www.test.com', $request->getHeaderLine('host'));
    }

    public function test_host_header_updates_from_uri_when_not_preserving_host(): void
    {
        $request = $this->request()->withAddedHeader('Host', 'example.com');
        $uri = $this->uri('')
            ->withHost('www.example.com')
            ->withPort(10081);

        $this->assertSame('www.example.com:10081', $request->withUri($uri)->getHeaderLine('Host'));
    }
}
