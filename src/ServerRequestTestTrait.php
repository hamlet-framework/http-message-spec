<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ServerRequestInterface;

trait ServerRequestTestTrait
{
    abstract protected function serverRequest(): ServerRequestInterface;

    public function test_defaults()
    {
        $request = $this->serverRequest();

        Assert::assertSame([], $request->getServerParams());
        Assert::assertSame([], $request->getQueryParams());
        Assert::assertSame([], $request->getCookieParams());
        Assert::assertSame([], $request->getUploadedFiles());
        Assert::assertSame(null, $request->getParsedBody());
        Assert::assertSame([], $request->getAttributes());
    }

    public function test_get_attribute_returns_default_value_only_when_attribute_not_preset()
    {
        $value = rand(1, 1000);

        $request1 = $this->serverRequest()->withAttribute('name', null);
        Assert::assertNull($request1->getAttribute('name', $value));

        $request2 = $request1->withoutAttribute('name');
        Assert::assertEquals($value, $request2->getAttribute('name', $value));
    }

    public function test_can_add_and_remove_null_attribute()
    {
        $request1 = $this->serverRequest()->withAttribute('name', null);
        Assert::assertSame(['name' => null], $request1->getAttributes());
        Assert::assertNull($request1->getAttribute('name', 'different-default'));

        $request2 = $request1->withoutAttribute('name');
        Assert::assertSame([], $request2->getAttributes());
    }

    public function test_removing_non_existent_attribute_does_not_raise_exception()
    {
        $request = $this->serverRequest();
        $request = $request->withoutAttribute('boo');
        Assert::assertSame([], $request->getAttributes());
    }

    public function test_immutability()
    {
        $request1 = $this->serverRequest();

        Assert::assertNotSame($request1, $request1->withQueryParams(['a' => '1']));
        Assert::assertNotSame($request1, $request1->withCookieParams(['a' => '1']));
        Assert::assertNotSame($request1, $request1->withUploadedFiles([]));
        Assert::assertNotSame($request1, $request1->withParsedBody([]));
        Assert::assertNotSame($request1, $request1->withAttribute('a', 1));

        $request2 = $request1->withAttribute('a', 1);
        Assert::assertNotSame($request2, $request2->withoutAttribute('a'));
    }

    /**
     * @dataProvider valid_query_params
     * @param $value
     */
    public function test_with_query_params_accepts_valid_values($value)
    {
        $params = null;
        parse_str($value, $params);

        $request = $this->serverRequest()->withQueryParams($params);
        Assert::assertSame($params, $request->getQueryParams());
    }

    /**
     * @dataProvider valid_cookie_params
     * @param $value
     */
    public function test_with_cookie_params_accepts_valid_values($value)
    {
        $request = $this->serverRequest()->withCookieParams($value);
        Assert::assertSame($value, $request->getCookieParams());
    }

    /**
     * @dataProvider valid_uploaded_files
     * @param $value
     */
    public function test_with_uploaded_files_accepts_valid_values($value)
    {
        $request = $this->serverRequest()->withUploadedFiles($value);
        Assert::assertSame($value, $request->getUploadedFiles());
    }

    /**
     * @dataProvider valid_parsed_bodies
     * @param $value
     */
    public function test_with_parsed_body_accepts_valid_values($value)
    {
        $request = $this->serverRequest()->withParsedBody($value);
        Assert::assertSame($value, $request->getParsedBody());
    }

    /**
     * @dataProvider valid_attribute_names_and_values
     * @param $name
     * @param $value
     */
    public function test_with_attribute_accepts_valid_names_and_values($name, $value)
    {
        $request = $this->serverRequest()->withAttribute($name, $value);
        Assert::assertSame($value, $request->getAttribute($name));
    }

    /**
     * @dataProvider valid_attribute_names_and_values
     * @param $name
     * @param $value
     */
    public function test_without_attribute_accept_valid_names($name, $value)
    {
        $value = rand(1, 100);

        $request = $this->serverRequest()->withAttribute($name, $value);
        Assert::assertSame([], $request->withoutAttribute($name)->getAttributes());
    }

    /**
     * @dataProvider invalid_query_params
     * @param $value
     */
    public function test_with_query_params_rejects_invalid_values($value)
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withQueryParams($value);
        $message->getQueryParams();
    }

    /**
     * @dataProvider invalid_cookie_params
     * @param $value
     */
    public function test_with_cookie_params_rejects_invalid_values($value)
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withCookieParams($value);
        $message->getCookieParams();
    }

    /**
     * @dataProvider invalid_uploaded_files
     * @param $value
     */
    public function test_with_uploaded_files_rejects_invalid_values($value)
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withUploadedFiles($value);
        $message->getUploadedFiles();
    }

    /**
     * @dataProvider invalid_parsed_bodies
     * @param $value
     */
    public function test_with_parsed_body_rejects_invalid_values($value)
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withParsedBody($value);
        $message->getParsedBody();
    }

    /**
     * @dataProvider invalid_attribute_names_and_values
     * @param $name
     * @param $value
     */
    public function test_with_attribute_accepts_rejects_invalid_values($name, $value)
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withAttribute($name, $value);
        $message->getAttributes();
    }

    /**
     * @dataProvider invalid_attribute_names_and_values
     * @param $name
     */
    public function test_without_attribute_rejects_invalid_values($name)
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withoutAttribute($name);
        $message->getAttributes();
    }
}
