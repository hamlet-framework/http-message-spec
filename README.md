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

## Benchmark results:

Comprehensive test

    +----------------------+--------------------+-------------------+--------------+
    | Function description | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | nyholm               | 2.1266             | 0.1440            | 10%          |
    | http-soft            | 2.3589             | 0.1200            | 8%           |
    | guzzle               | 2.5607             | 0.2390            | 16%          |
    | hamlet-framework     | 2.9768             | 0.3200            | 10%          |
    | ring-central         | 3.2848             | 0.0950            | 59%          |
    | zend-diactoros       | 5.2493             | 0.1790            | 16%          |
    | wind-walker          | 6.1238             | 0.3290            | 0            |
    +----------------------+--------------------+-------------------+--------------+

Fetching test

    +----------------------+--------------------+-------------------+--------------+
    | Function description | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | hamlet-framework     | 0.8523             | 0.0000            | 15%          |
    | nyholm               | 0.9859             | 0.0010            | 1%           |
    | ring-central         | 1.0014             | 0.0010            | 7%           |
    | guzzle               | 1.0766             | 0.0000            | 28%          |
    | http-soft            | 1.3821             | 0.0000            | 34%          |
    | zend-diactoros       | 1.8600             | 0.0000            | 5%           |
    | wind-walker          | 1.9627             | 0.0000            | 0            |
    +----------------------+--------------------+-------------------+--------------+

## Total number of test failures:

    +-----------------+-------+
    | Hamlet          |     0 |
    | Nyholm          |   192 |
    | WindWalker      |   244 |
    | Guzzle          |   328 |
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