<?php

declare(strict_types=1);

namespace Petfinder\Tests\Api;

use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Mock\Client;
use Http\Promise\Promise;
use Petfinder\Api\AnimalData;
use Petfinder\Result;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Petfinder\Api\AnimalData
 */
class AnimalDataTest extends TestCase
{
    /**
     * @var Client
     */
    private $http;

    /**
     * @var AnimalData
     */
    private $api;

    protected function setUp(): void
    {
        $this->http = new Client();
        $this->api = new AnimalData($this->http, new GuzzleMessageFactory());
    }

    /**
     * @covers ::typesAsync
     */
    public function testTypes(): void
    {
        $this->http->addResponse((new Response())->withBody(stream_for('{"types":["Dog","Cat","Bird"]}')));

        $promise = $this->api->typesAsync();
        $this->assertInstanceOf(Promise::class, $promise);

        $result = $promise->wait();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertCount(3, $result);
        $this->assertEquals('Cat', $result->search('types[1]'));

        $this->assertEquals('/types', $this->http->getLastRequest()->getUri()->getPath());
    }

    /**
     * @covers ::typeAsync
     */
    public function testType(): void
    {
        $this->http->addResponse((new Response())->withBody(stream_for('{"type":{}}')));

        $promise = $this->api->typeAsync('Dog');
        $this->assertInstanceOf(Promise::class, $promise);

        $result = $promise->wait();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals([], $result->search('type'));

        $this->assertEquals('/types/Dog', $this->http->getLastRequest()->getUri()->getPath());
    }

    /**
     * @covers ::breedsAsync
     */
    public function testBreeds(): void
    {
        $this->http->addResponse((new Response())->withBody(stream_for('{"breeds":["Beagle","Corgi","Husky"]}')));

        $promise = $this->api->breedsAsync('Dog');
        $this->assertInstanceOf(Promise::class, $promise);

        $result = $promise->wait();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertCount(3, $result);
        $this->assertEquals('Corgi', $result->search('breeds[1]'));

        $this->assertEquals('/types/Dog/breeds', $this->http->getLastRequest()->getUri()->getPath());
    }
}
