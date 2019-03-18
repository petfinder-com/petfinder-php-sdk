<?php

declare(strict_types=1);

namespace Petfinder\Tests\Api;

use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Mock\Client;
use Http\Promise\Promise;
use Petfinder\Api\AbstractApi;
use Petfinder\Result;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @coversDefaultClass \Petfinder\Api\AbstractApi
 */
class AbstractApiTest extends TestCase
{
    /**
     * @var Client
     */
    private $http;

    /**
     * @var TestApi
     */
    private $api;

    protected function setUp(): void
    {
        $this->http = new Client();
        $this->api = new TestApi($this->http, new GuzzleMessageFactory());
    }

    /**
     * @covers ::get
     */
    public function testGet(): void
    {
        $this->api->testAsync()->wait();

        $this->assertCount(1, $this->http->getRequests());
    }

    /**
     * @covers ::get
     */
    public function testGetWithParameters(): void
    {
        $this->api->testAsync(['foo' => 'bar'])->wait();

        $this->assertEquals('foo=bar', $this->http->getLastRequest()->getUri()->getQuery());
    }

    /**
     * @covers ::__construct
     * @covers ::__call
     */
    public function testSynchronousRequest(): void
    {
        $result = $this->api->test();

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * @covers ::__call
     */
    public function testMissingAsyncRequest(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Petfinder\Tests\Api\TestApi::foo()');

        $this->api->foo();
    }
}

/**
 * @method Result test()
 */
class TestApi extends AbstractApi
{
    public function testAsync(array $parameters = []): Promise
    {
        return $this->get('/', $parameters)->then(function (ResponseInterface $response) {
            return new Result($response);
        });
    }
}
