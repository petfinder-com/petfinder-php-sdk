<?php

declare(strict_types=1);

namespace Petfinder\Tests\Integration;

use Petfinder\Exception\ProblemDetailsException;
use Petfinder\Result;

/**
 * @group integration
 * @coversNothing
 */
class OrganizationTest extends TestCase
{
    public function testSearch(): void
    {
        $result = $this->client->organization->search();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(1, count($result));
        $this->assertArrayHasKey('name', $result->search('organizations[0]'));
    }

    public function testSearchWithParameters(): void
    {
        $result = $this->client->organization->search(['country' => 'US']);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(1, count($result));
        $this->assertEquals(['US'], array_unique($result->search('organizations[].address.country')));
    }

    public function testSearchInvalidParameters(): void
    {
        $this->expectException(ProblemDetailsException::class);

        $this->client->organization->search(['country' => 'Foobar']);
    }

    public function testShow(): void
    {
        $id = $this->client->organization->search()->search('organizations[0].id');
        $result = $this->client->organization->show($id);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($id, $result->search('organization.id'));
        $this->assertArrayHasKey('name', $result['organization']);
    }

    public function testShowNotFound(): void
    {
        $this->expectException(ProblemDetailsException::class);

        $this->client->organization->show('ABC123');
    }
}
