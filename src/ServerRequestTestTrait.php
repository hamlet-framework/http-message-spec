<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ServerRequestInterface;

trait ServerRequestTestTrait
{
    abstract protected function serverRequest(): ServerRequestInterface;

    public function test_defaults(): void
    {
        $request = $this->serverRequest();

        $this->assertSame([], $request->getServerParams());
        $this->assertSame([], $request->getQueryParams());
        $this->assertSame([], $request->getCookieParams());
        $this->assertSame([], $request->getUploadedFiles());
        $this->assertSame(null, $request->getParsedBody());
        $this->assertSame([], $request->getAttributes());
    }

    public function test_get_attribute_returns_default_value_only_when_attribute_not_preset(): void
    {
        $value = rand(1, 1000);

        $request1 = $this->serverRequest()->withAttribute('name', null);
        $this->assertNull($request1->getAttribute('name', $value));

        $request2 = $request1->withoutAttribute('name');
        $this->assertEquals($value, $request2->getAttribute('name', $value));
    }

    public function test_can_add_and_remove_null_attribute(): void
    {
        $request1 = $this->serverRequest()->withAttribute('name', null);
        $this->assertSame(['name' => null], $request1->getAttributes());
        $this->assertNull($request1->getAttribute('name', 'different-default'));

        $request2 = $request1->withoutAttribute('name');
        $this->assertSame([], $request2->getAttributes());
    }

    public function test_removing_non_existent_attribute_does_not_raise_exception(): void
    {
        $request = $this->serverRequest();
        $request = $request->withoutAttribute('boo');
        $this->assertSame([], $request->getAttributes());
    }

    public function test_immutability(): void
    {
        $request1 = $this->serverRequest();

        $this->assertNotSame($request1, $request1->withQueryParams(['a' => '1']));
        $this->assertNotSame($request1, $request1->withCookieParams(['a' => '1']));
        $this->assertNotSame($request1, $request1->withUploadedFiles([]));
        $this->assertNotSame($request1, $request1->withParsedBody([]));
        $this->assertNotSame($request1, $request1->withAttribute('a', 1));

        $request2 = $request1->withAttribute('a', 1);
        $this->assertNotSame($request2, $request2->withoutAttribute('a'));
    }

    #[DataProvider('valid_query_params')] public function test_with_query_params_accepts_valid_values(mixed $value): void
    {
        $params = null;
        parse_str($value, $params);

        $request = $this->serverRequest()->withQueryParams($params);
        $this->assertSame($params, $request->getQueryParams());
    }

    #[DataProvider('valid_cookie_params')] public function test_with_cookie_params_accepts_valid_values(mixed $value): void
    {
        $request = $this->serverRequest()->withCookieParams($value);
        $this->assertSame($value, $request->getCookieParams());
    }

    #[DataProvider('valid_uploaded_files')] public function test_with_uploaded_files_accepts_valid_values(mixed $value): void
    {
        $request = $this->serverRequest()->withUploadedFiles($value);
        $this->assertSame($value, $request->getUploadedFiles());
    }

    #[DataProvider('valid_parsed_bodies')] public function test_with_parsed_body_accepts_valid_values(mixed $value): void
    {
        $request = $this->serverRequest()->withParsedBody($value);
        $this->assertSame($value, $request->getParsedBody());
    }

    #[DataProvider('valid_attribute_names_and_values')] public function test_with_attribute_accepts_valid_names_and_values(mixed $name, mixed $value): void
    {
        $request = $this->serverRequest()->withAttribute($name, $value);
        $this->assertSame($value, $request->getAttribute($name));
    }

    #[DataProvider('valid_attribute_names_and_values')] public function test_without_attribute_accept_valid_names(mixed $name, mixed $value): void
    {
        $value = rand(1, 100);

        $request = $this->serverRequest()->withAttribute($name, $value);
        $this->assertSame([], $request->withoutAttribute($name)->getAttributes());
    }

    #[DataProvider('invalid_query_params')] public function test_with_query_params_rejects_invalid_values(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withQueryParams($value);
        $message->getQueryParams();
    }

    #[DataProvider('invalid_cookie_params')] public function test_with_cookie_params_rejects_invalid_values(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withCookieParams($value);
        $message->getCookieParams();
    }

    #[DataProvider('invalid_uploaded_files')] public function test_with_uploaded_files_rejects_invalid_values(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withUploadedFiles($value);
        $message->getUploadedFiles();
    }

    #[DataProvider('invalid_parsed_bodies')] public function test_with_parsed_body_rejects_invalid_values(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withParsedBody($value);
        $message->getParsedBody();
    }

    #[DataProvider('invalid_attribute_names_and_values')] public function test_with_attribute_accepts_rejects_invalid_values(mixed $name, mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withAttribute($name, $value);
        $message->getAttributes();
    }

    #[DataProvider('invalid_attribute_names_and_values')] public function test_without_attribute_rejects_invalid_values(mixed $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        $message = $this->serverRequest()->withoutAttribute($name);
        $message->getAttributes();
    }
}
