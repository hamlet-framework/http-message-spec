Hamlet Framework / HTTP / Message / Specification

Test case collection for PSR-7.

## How to use

```
"require-dev": {
    ...
    "hamlet-framework/http-message-spec": "@stable",
    ...
}
```

If you'd like to test your implementation of `Psr\Http\Message\RequestInterface` create a test case and import the following three traits:

```
class MyRequestTest extends TestCase
{
    use DataProviderTrait;
    use MessageTestTrait;
    use RequestTestTrait;

    ...
}
```

After that you'll have to implement 4 abstract factory methods returning your implementations of PSR-7 interfaces:

```
class MyRequestTest extends TestCase
{
    ...
    
    protected function request(): RequestInterface
    {
        return new MyRequest('GET', new Uri());
    }

    protected function message(): MessageInterface
    {
        return $this->request();
    }

    protected function stream(): StreamInterface
    {
        return MyStream();
    }

    protected function uri(string $value): UriInterface
    {
        return new MyUri($value);
    }
```

You can now run your tests.

## Benchmark results (PHP 8.3):

Comprehensive test

    +----------------------+--------------------+-------------------+--------------+
    | Function description | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | ring-central         | 1.1089             | 0.1770            | 13%          |
    | nyholm               | 1.2584             | 0.0870            | 20%          |
    | http-soft            | 1.5183             | 0.1210            | 5%           |
    | guzzle               | 1.6034             | 0.2050            | 18%          |
    | hamlet-framework     | 1.8983             | 0.1700            | 51%          |
    | zend-diactoros       | 2.8708             | 0.1730            | 8%           |
    | wind-walker          | 3.1030             | 0.5210            | 0            |
    +----------------------+--------------------+-------------------+--------------+

Fetching test

    +----------------------+--------------------+-------------------+--------------+
    | Function description | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | hamlet-framework     | 0.5370             | 0.0000            | 13%          |
    | ring-central         | 0.6111             | 0.0010            | 12%          |
    | guzzle               | 0.6852             | 0.0000            | 8%           |
    | nyholm               | 0.7433             | 0.0000            | 18%          |
    | http-soft            | 0.8771             | 0.0000            | 33%          |
    | zend-diactoros       | 1.1698             | 0.0000            | 32%          |
    | wind-walker          | 1.5468             | 0.0000            | 0            |
    +----------------------+--------------------+-------------------+--------------+


## Total number of test failures (PHP 8.3):

    +-----------------+-------+
    | Hamlet          |     0 |
    | Nyholm          |   108 |
    | RingCentral     |   109 |
    | Guzzle          |   111 |
    | HttpSoft        |   124 |
    | LaminasDiactoros |   140 |
    +-----------------+-------+

### Todo

- Add generic test for immutability trait
- Add more tests to URI from other projects
- Add tests from https://mathiasbynens.be/demo/url-regex
- Add tests from https://url.spec.whatwg.org/#parsing
- Add test for withUploadedFiles (the test cases are a bit thin at the moment)