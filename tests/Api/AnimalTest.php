<?php

declare(strict_types=1);

namespace Petfinder\Tests\Api;

use GuzzleHttp\Psr7\Response;
use function GuzzleHttp\Psr7\stream_for;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Mock\Client;
use Http\Promise\Promise;
use Petfinder\Api\Animal;
use Petfinder\Result;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Petfinder\Api\Animal
 */
class AnimalTest extends TestCase
{
    /**
     * @var Client
     */
    private $http;

    /**
     * @var Animal
     */
    private $api;

    protected function setUp(): void
    {
        $this->http = new Client();
        $this->api = new Animal($this->http, new GuzzleMessageFactory());
    }

    /**
     * @covers ::searchAsync
     */
    public function testSearch(): void
    {
        $this->http->addResponse((new Response())->withBody(stream_for('{"animals":{"foo":"bar","bar":"baz"}}')));

        $promise = $this->api->searchAsync(['type' => 'dog']);
        $this->assertInstanceOf(Promise::class, $promise);

        $result = $promise->wait();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('baz', $result->search('animals.bar'));

        $this->assertEquals('/animals', $this->http->getLastRequest()->getUri()->getPath());
        $this->assertEquals('type=dog', $this->http->getLastRequest()->getUri()->getQuery());
    }

    /**
     * @covers ::showAsync
     */
    public function testShow(): void
    {
        $this->http->addResponse((new Response())->withBody(stream_for('{"animal":{"name":"Spot","type":"Dog"}}')));

        $promise = $this->api->showAsync(1);
        $this->assertInstanceOf(Promise::class, $promise);

        $result = $promise->wait();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertCount(2, $result);
        $this->assertEquals('Spot', $result->search('animal.name'));

        $this->assertEquals('/animals/1', $this->http->getLastRequest()->getUri()->getPath());
    }
}
