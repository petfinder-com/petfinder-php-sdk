<?php

declare(strict_types=1);

namespace Petfinder\Tests\Http;

use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Petfinder\Exception\JsonException;
use Petfinder\Http\Json;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Petfinder\Http\Json
 */
class JsonTest extends TestCase
{
    /**
     * @covers ::decode
     */
    public function testDecode(): void
    {
        $response = (new Response())->withBody(stream_for('{"foo": "bar", "bar": "baz"}'));

        $json = Json::decode($response);

        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $json);
    }

    /**
     * @covers ::decode
     */
    public function testDecodeError(): void
    {
        $this->expectException(JsonException::class);

        Json::decode(new Response());
    }

    /**
     * @covers ::encode
     */
    public function testEncode(): void
    {
        $json = Json::encode(['greeting' => 'Hello', 'name' => 'World']);

        $this->assertEquals('{"greeting":"Hello","name":"World"}', $json);
    }

    /**
     * @covers ::encode
     */
    public function testEncodeError(): void
    {
        $this->expectException(JsonException::class);

        Json::encode("\x99");
    }
}
