<?php

declare(strict_types=1);

namespace Petfinder\Tests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use function GuzzleHttp\Psr7\stream_for;
use Http\Client\HttpAsyncClient;
use Http\Message\RequestMatcher\RequestMatcher;
use Petfinder\Api\Animal;
use Petfinder\Api\AnimalData;
use Petfinder\Api\Organization;
use Petfinder\Client;
use Petfinder\Exception\ProblemDetailsException;
use Petfinder\Http\Builder;
use Petfinder\Result;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Petfinder\Client
 */
class ClientTest extends TestCase
{
    /**
     * @var \Http\Mock\Client
     */
    private $http;

    /**
     * @var Client
     */
    private $client;

    protected function setUp(): void
    {
        $this->http = new \Http\Mock\Client();
        $this->client = new Client('foo', 'bar', new Builder($this->http));
    }

    /**
     * @covers ::__construct
     * @covers ::getHttpClient
     */
    public function testCreateClientWithoutHttpClient(): void
    {
        $client = new Client('foo', 'bar');

        $this->assertInstanceOf(HttpAsyncClient::class, $client->getHttpClient());
    }

    /**
     * @covers ::__construct
     * @covers ::getHttpClient
     */
    public function testCreateWithBuilder(): void
    {
        $client = new Client('foo', 'bar', new Builder($this->http));

        $client->getHttpClient()->sendAsyncRequest(new ServerRequest('GET', '/test'));

        $this->assertCount(1, $this->http->getRequests());
        $this->assertEquals('https://api.petfinder.com/v2/test', (string) $this->http->getLastRequest()->getUri());
    }

    /**
     * @covers ::__construct
     * @covers ::getHttpClient
     */
    public function testCreateWithBaseUrl(): void
    {
        $client = new Client('foo', 'bar', new Builder($this->http), 'http://www.example.com/test');

        $client->getHttpClient()->sendAsyncRequest(new ServerRequest('GET', '/foobar'));

        $this->assertCount(1, $this->http->getRequests());
        $this->assertEquals('http://www.example.com/test/foobar', (string) $this->http->getLastRequest()->getUri());
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticate(): void
    {
        $response = new Response(200, [], stream_for('{"access_token": "foobar", "expires_in": 3600}'));
        $this->http->on(new RequestMatcher('/v2/oauth2/token', 'api.petfinder.com', 'POST'), $response);

        $result = $this->client->authenticate();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals(3600, $result['expires_in']);

        $this->client->getHttpClient()->sendAsyncRequest(new Request('GET', '/test'));
        $this->assertEquals('Bearer foobar', $this->http->getLastRequest()->getHeaderLine('Authorization'));
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticateFail(): void
    {
        $this->expectException(ProblemDetailsException::class);
        $this->expectExceptionMessage('Error: Error occurred');

        $response = (new Response(401))
            ->withHeader('Content-Type', 'application/problem+json')
            ->withBody(stream_for('{"type": "test", "status": 401, "title": "Error", "detail": "Error occurred"}'));
        $this->http->addResponse($response);

        $this->client->authenticate();
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticateWithToken(): void
    {
        $this->client->authenticate('mytoken');

        $this->client->getHttpClient()->sendAsyncRequest(new Request('GET', '/test'));
        $this->assertEquals('Bearer mytoken', $this->http->getLastRequest()->getHeaderLine('Authorization'));
    }

    /**
     * @dataProvider dataApi
     *
     * @covers ::api
     * @covers ::ensureAuthenticated
     */
    public function testApi(string $name, string $expected): void
    {
        $this->client->authenticate('test');
        $api = $this->client->api($name);

        $this->assertInstanceOf($expected, $api);
    }

    public function dataApi(): \Generator
    {
        yield [Animal::class, Animal::class];
        yield ['animal', Animal::class];
        yield [AnimalData::class, AnimalData::class];
        yield ['animalData', AnimalData::class];
        yield [Organization::class, Organization::class];
        yield ['organization', Organization::class];
    }

    /**
     * @dataProvider dataApiInvalid
     *
     * @covers ::api
     */
    public function testThrowsExceptionOnInvalidApi(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('%s is not a valid Petfinder API.', $name));

        $this->client->api($name);
    }

    public function dataApiInvalid(): \Generator
    {
        yield [Client::class];
        yield ['dog'];
    }

    /**
     * @covers ::__get
     * @covers ::api
     */
    public function testGetApiProperty(): void
    {
        $this->client->authenticate('test');

        $this->assertInstanceOf(Animal::class, $this->client->animal);
        $this->assertInstanceOf(AnimalData::class, $this->client->animalData);
        $this->assertInstanceOf(Organization::class, $this->client->organization);
    }

    /**
     * @covers ::__get
     * @covers ::api
     */
    public function testGetInvalidApiProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('test is not a valid Petfinder API.');

        $this->client->test;
    }

    /**
     * @covers ::ensureAuthenticated
     * @covers ::api
     */
    public function testAuthenticatesWhenCreatingApi(): void
    {
        $this->http->addResponse(new Response(200, [], stream_for('{"access_token": "testing"}')));

        $this->client->api(Animal::class);

        $this->assertCount(1, $this->http->getRequests());
        $this->assertStringContainsString('/oauth2/token', $this->http->getLastRequest()->getUri()->getPath());
    }
}
