<?php

declare(strict_types=1);

namespace Petfinder;

use Http\Client\Common\Plugin\AddHostPlugin;
use Http\Client\Common\Plugin\AddPathPlugin;
use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\HttpAsyncClient;
use Http\Discovery\UriFactoryDiscovery;
use Petfinder\Api\AbstractApi;
use Petfinder\Api\Animal;
use Petfinder\Api\AnimalData;
use Petfinder\Api\Organization;
use Petfinder\Http\Builder;
use Petfinder\Http\Json;
use Petfinder\Http\Plugin\ProblemDetailsPlugin;
use Psr\Http\Message\ResponseInterface;

/**
 * @property Animal       $animal
 * @property AnimalData   $animalData
 * @property Organization $organization
 */
class Client
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var Builder
     */
    private $httpClientBuilder;

    /**
     * @var array
     */
    private $apis = [
        'animal' => Animal::class,
        'animalData' => AnimalData::class,
        'organization' => Organization::class,
    ];

    public function __construct(
        string $key,
        string $secret,
        ?Builder $builder = null,
        string $baseUrl = 'https://api.petfinder.com/v2'
    ) {
        $this->key = $key;
        $this->secret = $secret;
        $this->httpClientBuilder = $builder ?? new Builder();

        $uri = UriFactoryDiscovery::find()->createUri($baseUrl);

        $this->httpClientBuilder->addPlugin(new ProblemDetailsPlugin());
        $this->httpClientBuilder->addPlugin(new ErrorPlugin());
        $this->httpClientBuilder->addPlugin(new AddHostPlugin($uri));
        $this->httpClientBuilder->addPlugin(new AddPathPlugin($uri));
    }

    public function __get(string $name): AbstractApi
    {
        return $this->api($name);
    }

    public function authenticate(?string $token = null): ?Result
    {
        if ($token) {
            $this->httpClientBuilder->authenticate($token);

            return null;
        }

        $request = $this->httpClientBuilder->getRequestFactory()
            ->createRequest('POST', '/oauth2/token', [
                'Content-Type' => 'application/json',
            ], Json::encode([
                'grant_type' => 'client_credentials',
                'client_id' => $this->key,
                'client_secret' => $this->secret,
            ]));

        $token = $this->getHttpClient()->sendAsyncRequest($request)
            ->then(function (ResponseInterface $response) {
                return new Result($response, Json::decode($response));
            })->wait();

        $this->httpClientBuilder->authenticate($token['access_token']);

        return $token;
    }

    public function api(string $name): AbstractApi
    {
        $api = $this->apis[$name] ?? $name;

        if (!is_subclass_of($api, AbstractApi::class)) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid Petfinder API.', $name));
        }

        $this->ensureAuthenticated();

        return new $api($this->getHttpClient(), $this->httpClientBuilder->getRequestFactory());
    }

    public function getHttpClient(): HttpAsyncClient
    {
        return $this->httpClientBuilder->getHttpClient();
    }

    private function ensureAuthenticated(): void
    {
        if ($this->httpClientBuilder->isAuthenticated()) {
            return;
        }

        $this->authenticate();
    }
}
