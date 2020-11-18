Hamlet Framework / HTTP / Message / Specification

## Benchmark results:

Comprehensive test

    +----------------------+--------------------+-------------------+--------------+
    |                      | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | ring-central         | 3.8642             | 0.0970            | 26%          |
    | nyholm               | 4.8724             | 0.1470            | 6%           |
    | guzzle               | 5.1948             | 0.3000            | 22%          |
    | hamlet-framework     | 6.3398             | 0.3160            | 40%          |
    | http-soft            | 8.9373             | 0.1530            | 6%           |
    | zend-diactoros       | 9.5347             | 0.1780            | 18%          |
    | wind-walker          | 11.2797            | 0.3440            | 0            |
    +----------------------+--------------------+-------------------+--------------+

Fetching test

    +----------------------+--------------------+-------------------+--------------+
    |                      | Execution time (s) | Memory usage (mb) | Times faster |
    +----------------------+--------------------+-------------------+--------------+
    | hamlet-framework     | 1.8248             | 0.0000            | 22%          |
    | ring-central         | 2.2316             | 0.0000            | 7%           |
    | nyholm               | 2.4092             | 0.0000            | 6%           |
    | guzzle               | 2.5592             | 0.0000            | 36%          |
    | zend-diactoros       | 3.4990             | 0.0010            | 19%          |
    | wind-walker          | 4.1907             | 0.0010            | 0%           |
    | http-soft            | 4.1972             | 0.0000            | 0            |
    +----------------------+--------------------+-------------------+--------------+

## Total number of test failures:

    +-----------------+-------+
    | Hamlet          |     0 |
    | Nyholm          |   193 |
    | WindWalker      |   244 |
    | Guzzle          |   329 |
    | RingCentral     |   434 |
    | HttpSoft        |   439 |
    | ZendDiactoros   |   459 |
    | Slim            |  1387 |
    +-----------------+-------+

### Todo

- Add generic test for immutability trait
- Add more tests to URI from other projects
- Add tests from https://mathiasbynens.be/demo/url-regex
- Add tests from https://url.spec.whatwg.org/#parsing
