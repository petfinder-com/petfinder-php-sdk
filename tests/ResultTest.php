<?php

declare(strict_types=1);

namespace Petfinder\Tests;

use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Petfinder\Result;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Petfinder\Result
 */
class ResultTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getResponse
     */
    public function testGetResponse(): void
    {
        $response = new Response(204);
        $result = new Result($response);

        $this->assertSame($response, $result->getResponse());
    }

    /**
     * @covers ::__construct
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        $result = new Result(new Response(), ['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $result->toArray());

        $result = new Result(new Response(), ['foo' => 'bar'], 'test');
        $this->assertEquals(['foo' => 'bar'], $result->toArray());
    }

    /**
     * @covers ::__construct
     * @covers ::search
     */
    public function testSearch(): void
    {
        $result = new Result(new Response(), [
            'animals' => [
                [
                    'id' => 1,
                    'name' => 'Spot',
                    'url' => 'https://www.petfinder.com/dog/1-spot',
                ],
                [
                    'id' => 2,
                    'name' => 'Nebula',
                    'url' => 'https://www.petfinder.com/cat/2-nebula',
                ],
            ],
            'pagination' => [
                'total' => 2,
            ],
        ], 'animals');

        $this->assertEquals(['Spot', 'Nebula'], $result->search('animals[*].name'));
        $this->assertEquals('https://www.petfinder.com/dog/1-spot', $result->search('animals[0].url'));
    }

    /**
     * @covers ::getProtocolVersion
     * @covers ::getHeaders
     * @covers ::hasHeader
     * @covers ::getHeader
     * @covers ::getHeaderLine
     * @covers ::getBody
     * @covers ::getStatusCode
     * @covers ::getReasonPhrase
     */
    public function testIsHttpResponseImplementation(): void
    {
        $result = new Result(new Response(200, ['X-Test' => 'foobar'], stream_for('body')), ['foo' => 'bar']);

        $this->assertEquals('1.1', $result->getProtocolVersion());
        $this->assertEquals(['X-Test' => ['foobar']], $result->getHeaders());
        $this->assertFalse($result->hasHeader('Authorization'));
        $this->assertEquals(['foobar'], $result->getHeader('X-Test'));
        $this->assertEquals('foobar', $result->getHeaderLine('X-Test'));
        $this->assertEquals('body', $result->getBody()->getContents());
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('OK', $result->getReasonPhrase());
    }

    /**
     * @covers ::withProtocolVersion
     * @covers ::withHeader
     * @covers ::withAddedHeader
     * @covers ::withoutHeader
     * @covers ::withBody
     * @covers ::withStatus
     */
    public function testIsImmutableResponse(): void
    {
        $result = new Result(new Response(200, ['X-Test' => 'foobar'], stream_for('body')), ['foo' => 'bar']);

        $new = $result->withHeader('X-Powered-By', 'Petfinder');
        $this->assertInstanceOf(Result::class, $new);
        $this->assertNotSame($result, $new);

        $this->assertNotSame($result, $result->withProtocolVersion('2'));
        $this->assertNotSame($result, $result->withAddedHeader('X-Test', 'test'));
        $this->assertNotSame($result, $result->withoutHeader('X-Test'));
        $this->assertNotSame($result, $result->withBody(stream_for('test')));
        $this->assertNotSame($result, $result->withStatus(204));
    }

    /**
     * @covers ::getIterator
     */
    public function testIsIteratorAggregateImplementation(): void
    {
        $result = new Result(new Response(), ['foo' => 'bar']);

        $this->assertInstanceOf(\Traversable::class, $result->getIterator());

        foreach ($result as $key => $value) {
            $this->assertEquals('foo', $key);
            $this->assertEquals('bar', $value);
        }

        $keyed = new Result(new Response(), ['names' => ['Spot', 'Nebula'], 'foo' => 'bar'], 'names');

        $this->assertEquals(['Spot', 'Nebula'], (array) $keyed->getIterator());
    }

    /**
     * @covers ::offsetExists
     * @covers ::offsetGet
     * @covers ::offsetSet
     * @covers ::offsetUnset
     */
    public function testIsArrayAccessImplementation(): void
    {
        $result = new Result(new Response(), ['foo' => 'bar']);

        $this->assertTrue(isset($result['foo']));
        $this->assertEquals('bar', $result['foo']);

        $result['name'] = 'Spot';
        $this->assertEquals('Spot', $result['name']);

        unset($result['name']);
        $this->assertNull($result['name']);
    }

    /**
     * @covers ::count
     */
    public function testIsCountableImplementation(): void
    {
        $result = new Result(new Response(), ['foo' => 'bar']);
        $this->assertEquals(1, count($result));

        $keyed = new Result(new Response(), ['names' => ['Spot', 'Nebula'], 'foo' => 'bar', 'bar' => 'baz'], 'names');
        $this->assertCount(2, $keyed);
    }
}
