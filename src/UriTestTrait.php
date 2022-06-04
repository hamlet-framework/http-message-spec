<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

trait UriTestTrait
{
    abstract protected function uri($value = ''): UriInterface;

    /**
     * @noinspection DuplicatedCode
     */
    public function test_uri_parsing(): void
    {
        $uri = $this->uri('https://user:pass@example.com:8080/path/123?q=abc#test');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/123', $uri->getPath());
        $this->assertSame('q=abc', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string) $uri);
    }

    /**
     * @noinspection DuplicatedCode
     */
    public function test_set_and_retrieve_uri_components(): void
    {
        $uri = $this->uri()
            ->withScheme('https')
            ->withUserInfo('user', 'pass')
            ->withHost('example.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('q=abc')
            ->withFragment('test');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/123', $uri->getPath());
        $this->assertSame('q=abc', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string)$uri);
    }

    /**
     * @dataProvider valid_uris
     */
    public function test_valid_uris_forms_are_preserved(string $input): void
    {
        $uri = $this->uri($input);
        $this->assertSame($input, (string) $uri);
    }

    /**
     * @dataProvider valid_uri_schemes
     */
    public function test_valid_schemes_are_accepted(string $scheme): void
    {
        $uri = $this->uri()->withScheme($scheme);
        $this->assertSame($scheme, $uri->getScheme());
    }

    public function test_valid_ports_are_accepted(): void
    {
        $uri = $this->uri();
        for ($i = 0; $i < 1000; $i++) {
            $port = rand(0x0001, 0xffff);
            $uri2 = $uri->withPort($port);
            $this->assertEquals($port, $uri2->getPort());
        }
    }

    /**
     * @noinspection DuplicatedCode
     */
    public function test_recognize_falsey_uri_parts(): void
    {
        $uri = $this->uri('a://0:0@0/0?0#0');

        $this->assertSame('0:0@0', $uri->getAuthority());
        $this->assertSame('0:0', $uri->getUserInfo());
        $this->assertSame('0', $uri->getHost());
        $this->assertSame('/0', $uri->getPath());
        $this->assertSame('0', $uri->getQuery());
        $this->assertSame('0', $uri->getFragment());
        $this->assertSame('a://0:0@0/0?0#0', (string)$uri);
    }

    /**
     * @noinspection DuplicatedCode
     */
    public function test_accepts_falsey_uri_parts(): void
    {
        $uri = $this->uri()
            ->withScheme('a')
            ->withUserInfo('0', '0')
            ->withHost('0')
            ->withPath('/0')
            ->withQuery('0')
            ->withFragment('0');

        $this->assertSame('0:0@0', $uri->getAuthority());
        $this->assertSame('0:0', $uri->getUserInfo());
        $this->assertSame('0', $uri->getHost());
        $this->assertSame('/0', $uri->getPath());
        $this->assertSame('0', $uri->getQuery());
        $this->assertSame('0', $uri->getFragment());
        $this->assertSame('a://0:0@0/0?0#0', (string)$uri);
    }

    /**
     * @noinspection HttpUrlsUsage
     */
    public function test_scheme_is_normalized_to_lowercase(): void
    {
        $uri = $this->uri('HTTP://example.com');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('http://example.com', (string)$uri);

        $uri = $this->uri('//example.com')->withScheme('HTTP');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('http://example.com', (string)$uri);
    }

    public function test_host_is_normalized_to_lowercase(): void
    {
        $uri = $this->uri('//eXaMpLe.CoM');
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('//example.com', (string) $uri);

        $uri = $this->uri()->withHost('eXaMpLe.CoM');
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('//example.com', (string) $uri);
    }

    /**
     * @noinspection HttpUrlsUsage
     */
    public function test_port_is_null_for_standard_scheme_ports(): void
    {
        // HTTPS standard port
        $uri = $this->uri('https://example.com:443');
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());

        $uri = $this->uri('https://example.com')->withPort(443);
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());

        // HTTP standard port
        $uri = $this->uri('http://example.com:80');
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());

        $uri = $this->uri('http://example.com')->withPort(80);
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());
    }

    public function test_port_is_returned_if_scheme_unknown(): void
    {
        $uri = $this->uri('//example.com')->withPort(80);
        $this->assertSame(80, $uri->getPort());
        $this->assertSame('example.com:80', $uri->getAuthority());
    }

    /**
     * @noinspection HttpUrlsUsage
     */
    public function test_standard_port_resets_to_null_after_scheme_changes(): void
    {
        $uri = $this->uri('http://example.com:443');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame(443, $uri->getPort());
        $uri = $uri->withScheme('https');
        $this->assertNull($uri->getPort());
    }

    public function test_standard_port_is_preserved_in_case_schema_changes(): void
    {
        $uri = $this->uri('https://example.com:443');
        $this->assertNull($uri->getPort());

        $uri = $uri->withScheme('http');
        $this->assertEquals(443, $uri->getPort());
    }

    /**
     * @noinspection HttpUrlsUsage
     */
    public function test_port_can_be_removed(): void
    {
        $uri = $this->uri('http://example.com:8080')->withPort(null);
        $this->assertNull($uri->getPort());
        $this->assertSame('http://example.com', (string)$uri);
    }

    public function test_default_return_values_of_getters(): void
    {
        $uri = $this->uri();

        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', $uri->getFragment());
    }

    public function test_immutability(): void
    {
        $uri = $this->uri();

        $this->assertNotSame($uri, $uri->withScheme('https'));
        $this->assertNotSame($uri, $uri->withUserInfo('user', 'pass'));
        $this->assertNotSame($uri, $uri->withHost('example.com'));
        $this->assertNotSame($uri, $uri->withPort(8080));
        $this->assertNotSame($uri, $uri->withPath('/path/123'));
        $this->assertNotSame($uri, $uri->withQuery('q=abc'));
        $this->assertNotSame($uri, $uri->withFragment('test'));
    }

    public function test_relative_uris_are_recognized(): void
    {
        $uri = $this->uri()->withPath('foo');
        $this->assertSame('foo', $uri->getPath());
        $this->assertSame('foo', (string) $uri);
    }

    /**
     * @dataProvider uri_components
     */
    public function test_uri_components_encoding(string $input, string $path, string $query, string $fragment, string $output): void
    {
        $uri = $this->uri($input);

        $this->assertSame($path, $uri->getPath());
        $this->assertSame($query, $uri->getQuery());
        $this->assertSame($fragment, $uri->getFragment());
        $this->assertSame($output, (string)$uri);
    }

    public function test_with_fragment_encodes_value_properly(): void
    {
        $uri = $this->uri()->withFragment('#€?/b%61r');
        // A fragment starting with a "#" is valid and must not be magically removed. Otherwise, it would be impossible to
        // construct such a URI. Also the "?" and "/" does not need to be encoded in the fragment.
        $this->assertSame('%23%E2%82%AC?/b%61r', $uri->getFragment());
        $this->assertSame('#%23%E2%82%AC?/b%61r', (string)$uri);
    }

    public function test_adds_slash_for_relative_uri_string_with_host(): void
    {
        // If the path is rootless and an authority is present, the path MUST be prefixed by "/".
        $uri = $this->uri()->withPath('foo')->withHost('example.com');
        $this->assertSame('foo', $uri->getPath());

        // concatenating a relative path with a host doesn't work: "//example.comfoo" would be wrong
        $this->assertSame('//example.com/foo', (string) $uri);
    }

    public function test_remove_extra_slashes_without_host(): void
    {
        // If the path is starting with more than one "/" and no authority is
        // present, the starting slashes MUST be reduced to one.
        $uri = $this->uri()->withPath('//foo');
        $this->assertSame('//foo', $uri->getPath());
        // URI "//foo" would be interpreted as network reference and thus change the original path to the host
        $this->assertSame('/foo', (string) $uri);
    }

    public function test_authority_with_user_info_but_without_host(): void
    {
        $uri = $this->uri()->withUserInfo('user', 'pass');
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('', $uri->getAuthority());
    }

    public function test_with_path_encodes_properly(): void
    {
        $uri = $this->uri()->withPath('/baz?#€/b%61r');
        // Query and fragment delimiters and multibyte chars are encoded.
        $this->assertSame('/baz%3F%23%E2%82%AC/b%61r', $uri->getPath());
        $this->assertSame('/baz%3F%23%E2%82%AC/b%61r', (string)$uri);
    }

    public function test_with_query_encodes_properly(): void
    {
        $uri = $this->uri()->withQuery('?=#&€=/&b%61r');
        // A query starting with a "?" is valid and must not be magically removed. Otherwise, it would be impossible to
        // construct such a URI. Also the "?" and "/" does not need to be encoded in the query.
        $this->assertSame('?=%23&%E2%82%AC=/&b%61r', $uri->getQuery());
        $this->assertSame('??=%23&%E2%82%AC=/&b%61r', (string) $uri);
    }

    /**
     * @dataProvider invalid_uris
     */
    public function test_invalid_uris_are_rejected(mixed $uri): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->uri($uri);
    }

    /**
     * @dataProvider invalid_uri_schemes
     */
    public function test_with_scheme_rejects_invalid_values(mixed $scheme): void
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = $this->uri()->withScheme($scheme);
        $uri->getScheme();
    }

    /**
     * @dataProvider invalid_uri_user_infos
     */
    public function test_with_user_info_rejects_invalid_values(mixed $user, mixed $password): void
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = $this->uri()->withUserInfo($user, $password);
        $uri->getUserInfo();
    }

    /**
     * @dataProvider invalid_uri_hosts
     */
    public function test_with_host_rejects_invalid_values(mixed $host): void
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = $this->uri()->withHost($host);
        $uri->getHost();
    }

    /**
     * @dataProvider invalid_uri_ports
     */
    public function test_with_port_rejects_invalid_values(mixed $port): void
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = $this->uri()->withPort($port);
        $uri->getPort();
    }

    /**
     * @dataProvider invalid_uri_paths
     */
    public function test_with_path_rejects_invalid_values(mixed $path): void
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = $this->uri()->withPath($path);
        $uri->getPath();
    }

    /**
     * @dataProvider invalid_uri_queries
     */
    public function test_with_query_rejects_invalid_values(mixed $query): void
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = $this->uri()->withQuery($query);
        $uri->getQuery();
    }

    /**
     * @dataProvider invalid_uri_fragments
     */
    public function test_with_fragment_rejects_invalid_values(mixed $fragment): void
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = $this->uri()->withFragment($fragment);
        $uri->getFragment();
    }
}
