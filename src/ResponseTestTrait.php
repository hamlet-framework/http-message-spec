<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait ResponseTestTrait
{
    abstract protected function response(): ResponseInterface;

    public function test_default_properties(): void
    {
        $response = $this->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame([], $response->getHeaders());
        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('', (string) $response->getBody());
    }

    public function test_reason_phrase_deduced_from_status_code(): void
    {
        $response = $this->response()->withStatus(201);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Created', $response->getReasonPhrase());
    }

    public function test_with_status_code_and_reason(): void
    {
        $r = $this->response()->withStatus(201, 'Foo');
        $this->assertSame(201, $r->getStatusCode());
        $this->assertSame('Foo', $r->getReasonPhrase());

        $r = $this->response()->withStatus(201, '0');
        $this->assertSame(201, $r->getStatusCode());
        $this->assertSame('0', $r->getReasonPhrase(), 'Falsey reason works');
    }

    public function test_can_set_custom_code_and_reason_phrase(): void
    {
        $code = rand(100, 599);
        $reason = md5(random_bytes(32));

        $response = $this->response()->withStatus($code, $reason);
        $this->assertSame($code, $response->getStatusCode());
        $this->assertSame($reason, $response->getReasonPhrase());
    }

    #[DataProvider('invalid_reason_phrases')] public function test_with_status_rejects_invalid_reason_phrases(mixed $phrase): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = $this->response()->withStatus(422, $phrase);
        $response->getStatusCode();
        $response->getReasonPhrase();
    }

    public function test_with_status_accepts_valid_values(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $code = rand(100, 599);
            $response = $this->response()->withStatus($code);
            $result = $response->getStatusCode();
            $this->assertSame((int)$code, $result);
            $this->assertIsInt($result);
        }
    }

    #[DataProvider('invalid_status_codes')] public function test_with_status_rejects_invalid_codes(mixed $code): void
    {
        $this->expectException(InvalidArgumentException::class);

        $response = $this->response()->withStatus($code);
        $response->getStatusCode();
    }

    public function test_reason_phrase_for_unknown_code_is_empty_string(): void
    {
        $response = $this->response()->withStatus(555);

        $this->assertIsString($response->getReasonPhrase());
        $this->assertEmpty($response->getReasonPhrase());
    }
}
