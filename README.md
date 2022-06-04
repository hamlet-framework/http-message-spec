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

## Benchmark results (PHP 8.1.4):

Comprehensive test

    +----------------------+--------------------+-------------------+--------------+
    | Function description | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | ring-central         | 1.6762             | 0.1750            | 1%           |
    | nyholm               | 1.6955             | 0.1430            | 21%          |
    | http-soft            | 2.0541             | 0.1190            | 14%          |
    | guzzle               | 2.3434             | 0.2680            | 21%          |
    | hamlet-framework     | 2.8571             | 0.2340            | 75%          |
    | wind-walker          | 5.0028             | 0.3750            | 30%          |
    | zend-diactoros       | 6.5114             | 0.1740            | 0            |
    +----------------------+--------------------+-------------------+--------------+

Fetching test

    +----------------------+--------------------+-------------------+--------------+
    | Function description | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | hamlet-framework     | 0.6815             | 0.0000            | 22%          |
    | ring-central         | 0.8361             | 0.0010            | 5%           |
    | nyholm               | 0.8788             | 0.0010            | 10%          |
    | guzzle               | 0.9670             | 0.0000            | 9%           |
    | http-soft            | 1.0570             | 0.0000            | 50%          |
    | zend-diactoros       | 1.5899             | 0.0000            | 38%          |
    | wind-walker          | 2.1987             | 0.0000            | 0            |
    +----------------------+--------------------+-------------------+--------------+


## Total number of test failures (PHP 8.1.4):

    +-----------------+-------+
    | Hamlet          |     0 |
    | Nyholm          |   192 |
    | WindWalker      |   244 |
    | Guzzle          |   267 |
    | HttpSoft        |   435 |
    | RingCentral     |   435 |
    | ZendDiactoros   |   542 |
    | Slim            |  1387 |
    +-----------------+-------+

### Todo

- Add generic test for immutability trait
- Add more tests to URI from other projects
- Add tests from https://mathiasbynens.be/demo/url-regex
- Add tests from https://url.spec.whatwg.org/#parsing
- Add test for withUploadedFiles (the test cases are a bit thin at the moment)