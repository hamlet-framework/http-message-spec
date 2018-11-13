<?php

namespace Hamlet\Http\Message\Spec\Traits;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

trait ResponseTestTrait
{
    abstract protected function response(): ResponseInterface;

    public function testDefaultConstructor()
    {
        $r = self::response();
        $this->assertSame(200, $r->getStatusCode());
        $this->assertSame('1.1', $r->getProtocolVersion());
        $this->assertSame('OK', $r->getReasonPhrase());
        $this->assertSame([], $r->getHeaders());
        $this->assertInstanceOf('Psr\Http\Message\StreamInterface', $r->getBody());
        $this->assertSame('', (string)$r->getBody());
    }

    public function testWithStatusCodeAndNoReason()
    {
        $r = self::response()->withStatus(201);
        $this->assertSame(201, $r->getStatusCode());
        $this->assertSame('Created', $r->getReasonPhrase());
    }

    public function testWithStatusCodeAndReason()
    {
        $r = self::response()->withStatus(201, 'Foo');
        $this->assertSame(201, $r->getStatusCode());
        $this->assertSame('Foo', $r->getReasonPhrase());
        $r = self::response()->withStatus(201, '0');
        $this->assertSame(201, $r->getStatusCode());
        $this->assertSame('0', $r->getReasonPhrase(), 'Falsey reason works');
    }

    public function testWithProtocolVersion()
    {
        $r = self::response()->withProtocolVersion('1000');
        $this->assertSame('1000', $r->getProtocolVersion());
    }

    public function testSameInstanceWhenSameProtocol()
    {
        $r = self::response();
        $this->assertSame($r, $r->withProtocolVersion('1.1'));
    }

    public function testSameInstanceWhenSameBody()
    {
        $r = self::response();
        $b = $r->getBody();
        $this->assertSame($r, $r->withBody($b));
    }

    public function testWithHeader()
    {
        $r = self::response()->withStatus(200)->withHeader('Foo', 'Bar');
        $r2 = $r->withHeader('baZ', 'Bam');
        $this->assertSame(['Foo' => ['Bar']], $r->getHeaders());
        $this->assertSame(['Foo' => ['Bar'], 'baZ' => ['Bam']], $r2->getHeaders());
        $this->assertSame('Bam', $r2->getHeaderLine('baz'));
        $this->assertSame(['Bam'], $r2->getHeader('baz'));
    }

    public function testSameInstanceWhenRemovingMissingHeader()
    {
        $r = self::response();
        $this->assertSame($r, $r->withoutHeader('foo'));
    }

    public function testHeaderValuesAreTrimmed()
    {
        $r1 = self::response()->withHeader('OWS', " \t \tFoo\t \t ");
        $r2 = self::response()->withAddedHeader('OWS', " \t \tFoo\t \t ");;
        foreach ([$r1, $r2] as $r) {
            $this->assertSame(['OWS' => ['Foo']], $r->getHeaders());
            $this->assertSame('Foo', $r->getHeaderLine('OWS'));
            $this->assertSame(['Foo'], $r->getHeader('OWS'));
        }
    }

    public function testStatusCodeIs200ByDefault()
    {
        $this->assertSame(200, self::response()->getStatusCode());
    }

    public function testStatusCodeMutatorReturnsCloneWithChanges()
    {
        $response = self::response()->withStatus(400);
        $this->assertNotSame(self::response(), $response);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testReasonPhraseDefaultsToStandards()
    {
        $response = self::response()->withStatus(422);
        $this->assertSame('Unprocessable Entity', $response->getReasonPhrase());
    }

    public function testCanSetCustomReasonPhrase()
    {
        $response = self::response()->withStatus(422, 'Foo Bar!');
        $this->assertSame('Foo Bar!', $response->getReasonPhrase());
    }

    public function invalidReasonPhrases()
    {
        return [
            'true' => [true],
            'false' => [false],
            'array' => [[200]],
            'object' => [(object)['reasonPhrase' => 'Ok']],
            'integer' => [99],
            'float' => [400.5],
            'null' => [null],
        ];
    }

    /**
     * @dataProvider invalidReasonPhrases
     * @param $invalidReasonPhrase
     */
    public function testWithStatusRaisesAnExceptionForNonStringReasonPhrases($invalidReasonPhrase)
    {
        $this->expectException(InvalidArgumentException::class);
        self::response()->withStatus(422, $invalidReasonPhrase);
    }

    /**
     * @dataProvider validStatusCodes
     * @param $code
     */
    public function testCreateWithValidStatusCodes($code)
    {
        $response = self::response()->withStatus($code);
        $result = $response->getStatusCode();
        $this->assertSame((int)$code, $result);
        $this->assertInternalType('int', $result);
    }

    public function validStatusCodes()
    {
        return [
            'minimum' => [100],
            'middle' => [300],
            'string-integer' => ['300'],
            'maximum' => [599],
        ];
    }

    /**
     * @dataProvider invalidStatusCodes
     * @param $code
     */
    public function testCannotSetInvalidStatusCode($code)
    {
        $this->expectException(InvalidArgumentException::class);
        self::response()->withStatus($code);
    }

    public function invalidStatusCodes()
    {
        return [
            'true' => [true],
            'false' => [false],
            'array' => [[200]],
            'object' => [(object)['statusCode' => 200]],
            'too-low' => [99],
            'float' => [400.5],
            'too-high' => [600],
            'null' => [null],
            'string' => ['foo'],
        ];
    }

    public function invalidResponseBody()
    {
        return [
            'true' => [true],
            'false' => [false],
            'int' => [1],
            'float' => [1.1],
            'array' => [['BODY']],
            'stdClass' => [(object)['body' => 'BODY']],
        ];
    }

    /**
     * @dataProvider invalidResponseBody
     * @param $body
     */
    public function testConstructorRaisesExceptionForInvalidBody($body)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stream');
        self::response()->withBody($body);
    }

    public function invalidHeaderTypes()
    {
        return [
            'indexed-array' => [[['INVALID']], 'header name'],
            'null' => [['x-invalid-null' => null]],
            'true' => [['x-invalid-true' => true]],
            'false' => [['x-invalid-false' => false]],
            'object' => [['x-invalid-object' => (object)['INVALID']]],
        ];
    }

    public function testReasonPhraseCanBeEmpty()
    {
        $response = self::response()->withStatus(555);
        $this->assertInternalType('string', $response->getReasonPhrase());
        $this->assertEmpty($response->getReasonPhrase());
    }

    public function headersWithInjectionVectors()
    {
        return [
            'name-with-cr' => ["X-Foo\r-Bar", 'value'],
            'name-with-lf' => ["X-Foo\n-Bar", 'value'],
            'name-with-crlf' => ["X-Foo\r\n-Bar", 'value'],
            'name-with-2crlf' => ["X-Foo\r\n\r\n-Bar", 'value'],
            'value-with-cr' => ['X-Foo-Bar', "value\rinjection"],
            'value-with-lf' => ['X-Foo-Bar', "value\ninjection"],
            'value-with-crlf' => ['X-Foo-Bar', "value\r\ninjection"],
            'value-with-2crlf' => ['X-Foo-Bar', "value\r\n\r\ninjection"],
            'array-value-with-cr' => ['X-Foo-Bar', ["value\rinjection"]],
            'array-value-with-lf' => ['X-Foo-Bar', ["value\ninjection"]],
            'array-value-with-crlf' => ['X-Foo-Bar', ["value\r\ninjection"]],
            'array-value-with-2crlf' => ['X-Foo-Bar', ["value\r\n\r\ninjection"]],
        ];
    }

    /**
     * @group ZF2015-04
     * @dataProvider headersWithInjectionVectors
     * @param $name
     * @param $value
     */
    public function testConstructorRaisesExceptionForHeadersWithCRLFVectors($name, $value)
    {
        $this->expectException(InvalidArgumentException::class);
        self::response()->withHeader($name, $value);
    }

    public function testDisableSetter()
    {
        $response = self::response();
        $response->foo = 'bar';
        $this->assertFalse(property_exists($response, 'foo'));
    }

    /*******************************************************************************
     * Status
     ******************************************************************************/

    public function testWithStatus()
    {
        $response = self::response();
        $clone = $response->withStatus(302);
        $this->assertAttributeEquals(302, 'status', $clone);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithStatusInvalidStatusCodeThrowsException()
    {
        $response = self::response();
        $response->withStatus(800);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage ReasonPhrase must be a string
     */
    public function testWithStatusInvalidReasonPhraseThrowsException()
    {
        $response = self::response();
        $response->withStatus(200, null);
    }

    public function testWithStatusEmptyReasonPhrase()
    {
        $responseWithNoMessage = self::response()->withStatus(310);
        $this->assertEquals('', $responseWithNoMessage->getReasonPhrase());
    }

    public function testGetReasonPhrase()
    {
        $response = self::response()->withStatus(404);
        $this->assertEquals('Not Found', $response->getReasonPhrase());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage ReasonPhrase must be supplied for this code
     */
    public function testMustSetReasonPhraseForUnrecognisedCode()
    {
        $response = self::response();
        $response = $response->withStatus(199);
    }

    public function testSetReasonPhraseForUnrecognisedCode()
    {
        $response = self::response();
        $response = $response->withStatus(199, 'Random Message');
        $this->assertEquals('Random Message', $response->getReasonPhrase());
    }

    public function testGetCustomReasonPhrase()
    {
        $response = self::response();
        $clone = $response->withStatus(200, 'Custom Phrase');
        $this->assertEquals('Custom Phrase', $clone->getReasonPhrase());
    }

    public function testWithAndGetStatusCode()
    {
        $response = self::response();

        $this->assertEquals(200, $response->getStatusCode());
        $res = $response->withStatus(403);
        $this->assertNotSame($res, $response);
        $this->assertEquals(403, $res->getStatusCode());
        $res = $res->withStatus(500, 'Unknown error');
        $this->assertEquals(500, $res->getStatusCode());
        $this->assertEquals('Unknown error', $res->getReasonPhrase());
    }

    public function testGetReasonPhrase2()
    {
        $res = self::response();
        $res = $res->withStatus(200);
        $this->assertEquals('OK', $res->getReasonPhrase());
        $res = $res->withStatus(400);
        $this->assertEquals('Bad Request', $res->getReasonPhrase());
        $res = $res->withStatus(404);
        $this->assertEquals('Not Found', $res->getReasonPhrase());
        $res = $res->withStatus(500);
        $this->assertEquals('Internal Server Error', $res->getReasonPhrase());
    }
}
