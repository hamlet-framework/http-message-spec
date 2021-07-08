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

## Benchmark results (PHP 8.0.7):

Comprehensive test

    +----------------------+--------------------+-------------------+--------------+
    | Function description | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | ring-central         | 2.1037             | 0.1590            | 6%           |
    | nyholm               | 2.2324             | 0.1210            | 28%          |
    | http-soft            | 2.8637             | 0.2040            | 4%           |
    | guzzle               | 2.9965             | 0.2180            | 9%           |
    | hamlet-framework     | 3.2867             | 0.2370            | 73%          |
    | wind-walker          | 5.7143             | 0.3210            | 1%           |
    | zend-diactoros       | 5.8252             | 0.1790            | 0            |
    +----------------------+--------------------+-------------------+--------------+

Fetching test

    +----------------------+--------------------+-------------------+--------------+
    | Function description | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | hamlet-framework     | 0.8763             | 0.0010            | 24%          |
    | ring-central         | 1.0887             | 0.0000            | 0%           |
    | nyholm               | 1.0920             | 0.0000            | 17%          |
    | guzzle               | 1.2785             | 0.0010            | 10%          |
    | http-soft            | 1.4137             | 0.0000            | 40%          |
    | zend-diactoros       | 1.9847             | 0.0000            | 5%           |
    | wind-walker          | 2.0999             | 0.0000            | 0            |
    +----------------------+--------------------+-------------------+--------------+

## Total number of test failures (PHP 8.0.7):

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