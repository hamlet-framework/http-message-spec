<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\UriInterface;

trait UriTestTrait
{
    abstract protected function uri($value = ''): UriInterface;

    // @todo change to something more elaborate
    public function test_uri_parsing()
    {
        $uri = self::uri('https://user:pass@example.com:8080/path/123?q=abc#test');

        Assert::assertSame('https', $uri->getScheme());
        Assert::assertSame('user:pass@example.com:8080', $uri->getAuthority());
        Assert::assertSame('user:pass', $uri->getUserInfo());
        Assert::assertSame('example.com', $uri->getHost());
        Assert::assertSame(8080, $uri->getPort());
        Assert::assertSame('/path/123', $uri->getPath());
        Assert::assertSame('q=abc', $uri->getQuery());
        Assert::assertSame('test', $uri->getFragment());
        Assert::assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string) $uri);
    }

    public function test_set_and_retrieve_uri_components()
    {
        $uri = self::uri()
            ->withScheme('https')
            ->withUserInfo('user', 'pass')
            ->withHost('example.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('q=abc')
            ->withFragment('test');

        Assert::assertSame('https', $uri->getScheme());
        Assert::assertSame('user:pass@example.com:8080', $uri->getAuthority());
        Assert::assertSame('user:pass', $uri->getUserInfo());
        Assert::assertSame('example.com', $uri->getHost());
        Assert::assertSame(8080, $uri->getPort());
        Assert::assertSame('/path/123', $uri->getPath());
        Assert::assertSame('q=abc', $uri->getQuery());
        Assert::assertSame('test', $uri->getFragment());
        Assert::assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string)$uri);
    }

    /**
     * @dataProvider valid_uris
     * @param string $input
     */
    public function test_valid_uris_forms_are_preserved(string $input)
    {
        $uri = self::uri($input);
        Assert::assertSame($input, (string) $uri);
    }

    public function test_valid_ports_are_accepted()
    {
        $uri = self::uri();
        for ($i = 0; $i < 1000; $i++) {
            $port = rand(0x0001, 0xffff);
            $uri2 = $uri->withPort($port);
            Assert::assertEquals($port, $uri2->getPort());
        }
    }

    public function test_recognize_falsey_uri_parts()
    {
        $uri = self::uri('a://0:0@0/0?0#0');

        Assert::assertSame('0:0@0', $uri->getAuthority());
        Assert::assertSame('0:0', $uri->getUserInfo());
        Assert::assertSame('0', $uri->getHost());
        Assert::assertSame('/0', $uri->getPath());
        Assert::assertSame('0', $uri->getQuery());
        Assert::assertSame('0', $uri->getFragment());
        Assert::assertSame('a://0:0@0/0?0#0', (string)$uri);
    }

    public function test_accepts_falsey_uri_parts()
    {
        $uri = self::uri()
            ->withScheme('a')
            ->withUserInfo('0', '0')
            ->withHost('0')
            ->withPath('/0')
            ->withQuery('0')
            ->withFragment('0');

        Assert::assertSame('0:0@0', $uri->getAuthority());
        Assert::assertSame('0:0', $uri->getUserInfo());
        Assert::assertSame('0', $uri->getHost());
        Assert::assertSame('/0', $uri->getPath());
        Assert::assertSame('0', $uri->getQuery());
        Assert::assertSame('0', $uri->getFragment());
        Assert::assertSame('a://0:0@0/0?0#0', (string)$uri);
    }

    public function test_scheme_is_normalized_to_lowercase()
    {
        $uri = self::uri('HTTP://example.com');
        Assert::assertSame('http', $uri->getScheme());
        Assert::assertSame('http://example.com', (string)$uri);

        $uri = self::uri('//example.com')->withScheme('HTTP');
        Assert::assertSame('http', $uri->getScheme());
        Assert::assertSame('http://example.com', (string)$uri);
    }

    public function test_host_is_normalized_to_lowercase()
    {
        $uri = self::uri('//eXaMpLe.CoM');
        Assert::assertSame('example.com', $uri->getHost());
        Assert::assertSame('//example.com', (string) $uri);

        $uri = self::uri()->withHost('eXaMpLe.CoM');
        Assert::assertSame('example.com', $uri->getHost());
        Assert::assertSame('//example.com', (string) $uri);
    }

    public function test_port_is_null_for_standard_scheme_ports()
    {
        // HTTPS standard port
        $uri = self::uri('https://example.com:443');
        Assert::assertNull($uri->getPort());
        Assert::assertSame('example.com', $uri->getAuthority());

        $uri = self::uri('https://example.com')->withPort(443);
        Assert::assertNull($uri->getPort());
        Assert::assertSame('example.com', $uri->getAuthority());

        // HTTP standard port
        $uri = self::uri('http://example.com:80');
        Assert::assertNull($uri->getPort());
        Assert::assertSame('example.com', $uri->getAuthority());

        $uri = self::uri('http://example.com')->withPort(80);
        Assert::assertNull($uri->getPort());
        Assert::assertSame('example.com', $uri->getAuthority());
    }

    public function test_port_is_returned_if_scheme_unknown()
    {
        $uri = self::uri('//example.com')->withPort(80);
        Assert::assertSame(80, $uri->getPort());
        Assert::assertSame('example.com:80', $uri->getAuthority());
    }

    public function test_standard_port_resets_to_null_after_scheme_changes()
    {
        $uri = self::uri('http://example.com:443');
        Assert::assertSame('http', $uri->getScheme());
        Assert::assertSame(443, $uri->getPort());
        $uri = $uri->withScheme('https');
        Assert::assertNull($uri->getPort());
    }

    public function test_port_can_be_removed()
    {
        $uri = self::uri('http://example.com:8080')->withPort(null);
        Assert::assertNull($uri->getPort());
        Assert::assertSame('http://example.com', (string)$uri);
    }

    public function test_default_return_values_of_getters()
    {
        $uri = self::uri();

        Assert::assertSame('', $uri->getScheme());
        Assert::assertSame('', $uri->getAuthority());
        Assert::assertSame('', $uri->getUserInfo());
        Assert::assertSame('', $uri->getHost());
        Assert::assertNull($uri->getPort());
        Assert::assertSame('', $uri->getPath());
        Assert::assertSame('', $uri->getQuery());
        Assert::assertSame('', $uri->getFragment());
    }

    public function test_immutability()
    {
        $uri = self::uri();

        Assert::assertNotSame($uri, $uri->withScheme('https'));
        Assert::assertNotSame($uri, $uri->withUserInfo('user', 'pass'));
        Assert::assertNotSame($uri, $uri->withHost('example.com'));
        Assert::assertNotSame($uri, $uri->withPort(8080));
        Assert::assertNotSame($uri, $uri->withPath('/path/123'));
        Assert::assertNotSame($uri, $uri->withQuery('q=abc'));
        Assert::assertNotSame($uri, $uri->withFragment('test'));
    }

    public function test_relative_uris_are_recognized()
    {
        $uri = self::uri()->withPath('foo');
        Assert::assertSame('foo', $uri->getPath());
        Assert::assertSame('foo', (string) $uri);
    }

    /**
     * @dataProvider uri_components
     * @param string $input
     * @param string $path
     * @param string $query
     * @param string $fragment
     * @param string $output
     */
    public function test_uri_components_encoding(string $input, string $path, string $query, string $fragment, string $output)
    {
        $uri = self::uri($input);

        Assert::assertSame($path, $uri->getPath());
        Assert::assertSame($query, $uri->getQuery());
        Assert::assertSame($fragment, $uri->getFragment());
        Assert::assertSame($output, (string)$uri);
    }

    public function test_with_fragment_encodes_value_properly()
    {
        $uri = self::uri()->withFragment('#€?/b%61r');
        // A fragment starting with a "#" is valid and must not be magically removed. Otherwise it would be impossible to
        // construct such an URI. Also the "?" and "/" does not need to be encoded in the fragment.
        Assert::assertSame('%23%E2%82%AC?/b%61r', $uri->getFragment());
        Assert::assertSame('#%23%E2%82%AC?/b%61r', (string)$uri);
    }

    public function test_adds_slash_for_relative_uri_string_with_host()
    {
        // If the path is rootless and an authority is present, the path MUST be prefixed by "/".
        $uri = self::uri()->withPath('foo')->withHost('example.com');
        Assert::assertSame('foo', $uri->getPath());

        // concatenating a relative path with a host doesn't work: "//example.comfoo" would be wrong
        Assert::assertSame('//example.com/foo', (string) $uri);
    }

    public function test_remove_extra_slashes_without_host()
    {
        // If the path is starting with more than one "/" and no authority is
        // present, the starting slashes MUST be reduced to one.
        $uri = self::uri()->withPath('//foo');
        Assert::assertSame('//foo', $uri->getPath());
        // URI "//foo" would be interpreted as network reference and thus change the original path to the host
        Assert::assertSame('/foo', (string) $uri);
    }

    public function test_authority_with_user_info_but_without_host()
    {
        $uri = self::uri()->withUserInfo('user', 'pass');
        Assert::assertSame('user:pass', $uri->getUserInfo());
        Assert::assertSame('', $uri->getAuthority());
    }

    public function test_with_path_encodes_properly()
    {
        $uri = self::uri()->withPath('/baz?#€/b%61r');
        // Query and fragment delimiters and multibyte chars are encoded.
        Assert::assertSame('/baz%3F%23%E2%82%AC/b%61r', $uri->getPath());
        Assert::assertSame('/baz%3F%23%E2%82%AC/b%61r', (string)$uri);
    }

    public function test_with_query_encodes_properly()
    {
        $uri = self::uri()->withQuery('?=#&€=/&b%61r');
        // A query starting with a "?" is valid and must not be magically removed. Otherwise it would be impossible to
        // construct such an URI. Also the "?" and "/" does not need to be encoded in the query.
        Assert::assertSame('?=%23&%E2%82%AC=/&b%61r', $uri->getQuery());
        Assert::assertSame('??=%23&%E2%82%AC=/&b%61r', (string) $uri);
    }

    public function test_immutabilit_y()
    {
        $uri = self::uri();

        Assert::assertNotSame($uri, $uri->withScheme('https'));
        Assert::assertNotSame($uri, $uri->withUserInfo('user', 'pass'));
        Assert::assertNotSame($uri, $uri->withHost('example.com'));
        Assert::assertNotSame($uri, $uri->withPort(8080));
        Assert::assertNotSame($uri, $uri->withPath('/path/123'));
        Assert::assertNotSame($uri, $uri->withQuery('q=abc'));
        Assert::assertNotSame($uri, $uri->withFragment('test'));
    }

    /**
     * @dataProvider invalid_uris
     * @param string $uri
     * @expectedException InvalidArgumentException
     */
    public function test_invalid_uris_are_rejected($uri)
    {
        self::uri($uri);
    }

    /**
     * @dataProvider invalid_uri_schemes
     * @param mixed $scheme
     * @expectedException InvalidArgumentException
     */
    public function test_with_scheme_rejects_invalid_values($scheme)
    {
        $uri = self::uri()->withScheme($scheme);
        $uri->getScheme();
    }

    /**
     * @dataProvider invalid_uri_user_infos
     * @param mixed $user
     * @param mixed $password
     * @expectedException InvalidArgumentException
     */
    public function test_with_user_info_rejects_invalid_values($user, $password)
    {
        $uri = self::uri()->withUserInfo($user, $password);
        $uri->getUserInfo();
    }

    /**
     * @dataProvider invalid_uri_hosts
     * @param mixed $host
     * @expectedException InvalidArgumentException
     */
    public function test_with_host_rejects_invalid_values($host)
    {
        $uri = self::uri()->withHost($host);
        $uri->getHost();
    }

    /**
     * @dataProvider invalid_uri_ports
     * @param mixed $port
     * @expectedException InvalidArgumentException
     */
    public function test_with_port_rejects_invalid_values($port)
    {
        $uri = self::uri()->withPort($port);
        $uri->getPort();
    }

    /**
     * @dataProvider invalid_uri_paths
     * @param mixed $path
     * @expectedException InvalidArgumentException
     */
    public function test_with_path_rejects_invalid_values($path)
    {
        $uri = self::uri()->withPath($path);
        $uri->getPath();
    }

    /**
     * @dataProvider invalid_uri_queries
     * @param mixed $query
     * @expectedException InvalidArgumentException
     */
    public function test_with_query_rejects_invalid_values($query)
    {
        $uri = self::uri()->withQuery($query);
        $uri->getQuery();
    }

    /**
     * @dataProvider invalid_uri_fragments
     * @param mixed $fragment
     * @expectedException InvalidArgumentException
     */
    public function test_with_fragment_rejects_invalid_values($fragment)
    {
        $uri = self::uri()->withFragment($fragment);
        $uri->getFragment();
    }
}
