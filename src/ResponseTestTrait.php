<?php

namespace Hamlet\Http\Message\Spec\Traits;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait ResponseTestTrait
{
    abstract protected function response(): ResponseInterface;

    public function test_default_properties()
    {
        $response = $this->response();
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('1.1', $response->getProtocolVersion());
        Assert::assertSame('OK', $response->getReasonPhrase());
        Assert::assertSame([], $response->getHeaders());
        Assert::assertInstanceOf(StreamInterface::class, $response->getBody());
        Assert::assertSame('', (string) $response->getBody());
    }

    // @todo loop though values
    public function test_reason_phrase_deduced_from_status_code()
    {
        $response = $this->response()->withStatus(201);

        Assert::assertSame(201, $response->getStatusCode());
        Assert::assertSame('Created', $response->getReasonPhrase());
    }

    public function test_with_status_code_and_reason()
    {
        $r = $this->response()->withStatus(201, 'Foo');
        Assert::assertSame(201, $r->getStatusCode());
        Assert::assertSame('Foo', $r->getReasonPhrase());

        $r = $this->response()->withStatus(201, '0');
        Assert::assertSame(201, $r->getStatusCode());
        Assert::assertSame('0', $r->getReasonPhrase(), 'Falsey reason works');
    }

    /**
     * @throws Exception
     */
    public function test_can_set_custom_code_and_reason_phrase()
    {
        $code = rand(100, 999);
        $reason = md5(random_bytes(32));

        $response = $this->response()->withStatus($code, $reason);
        Assert::assertSame($code, $response->getStatusCode());
        Assert::assertSame($reason, $response->getReasonPhrase());
    }

    /**
     * @dataProvider invalid_reason_phrases
     * @expectedException InvalidArgumentException
     * @param $phrase
     */
    public function test_with_status_rejects_invalid_reason_phrases($phrase)
    {
        $this->response()->withStatus(422, $phrase);
    }

    public function test_with_status_accepts_valid_values()
    {
        for ($i = 0; $i < 100; $i++) {
            $code = rand(100, 999);
            $response = $this->response()->withStatus($code);
            $result = $response->getStatusCode();
            Assert::assertSame((int)$code, $result);
            Assert::assertInternalType('int', $result);
        }
    }

    /**
     * @dataProvider invalid_status_codes
     * @expectedException InvalidArgumentException
     * @param $code
     */
    public function test_with_status_rejects_invalid_codes($code)
    {
        $response = $this->response()->withStatus($code);
        $response->getStatusCode();
    }

    public function test_reason_phrase_for_unknown_code_is_empty_string()
    {
        $response = $this->response()->withStatus(555);

        Assert::assertInternalType('string', $response->getReasonPhrase());
        Assert::assertEmpty($response->getReasonPhrase());
    }
}
