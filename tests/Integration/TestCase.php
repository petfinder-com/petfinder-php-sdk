<?php

declare(strict_types=1);

namespace Petfinder\Tests\Integration;

use Petfinder\Client;
use Petfinder\Http\Builder;

/**
 * Test case for integration tests to Petfinder API.
 *
 * These make real API requests and require that you configure an API key and
 * secret. If not, these tests will be skipped.
 *
 * @group integration
 * @coversNothing
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string|null
     */
    private static $token;

    /**
     * @var Client
     */
    protected $client;

    protected function setUp(): void
    {
        $apiKey = getenv('PETFINDER_API_KEY');
        $secret = getenv('PETFINDER_API_SECRET');

        if (!$apiKey || !$secret) {
            $this->markTestSkipped('Test requires authentication. Skipping to prevent unnecessary failure.');
        }

        $builder = new Builder();
        $builder->addHeaders(['User-Agent' => 'petfinder-php-sdk-test']);

        $client = new Client($apiKey, $secret, $builder, getenv('PETFINDER_API_URL') ?: 'https://api.petfinder.com/v2');

        if (!self::$token) {
            self::$token = $client->authenticate()['access_token'];
        }

        $client->authenticate(self::$token);
        $this->client = $client;
    }
}
