<?php

namespace Tests\Unit\Concerns;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Spinen\Ncentral\Api\Client as Ncentral;
use Spinen\Ncentral\Concerns\HasClient;
use Spinen\Ncentral\Exceptions\NoClientException;
use Spinen\Ncentral\User;
use Tests\TestCase;
use Tests\Unit\Concerns\Stubs\ItemNeedingClient;

class HasClientTest extends TestCase
{
    protected $client_mock;

    protected $trait;

    protected function setUp(): void
    {
        $this->client_mock = Mockery::mock(Ncentral::class);

        $this->trait = new ItemNeedingClient;
    }

    #[Test]
    public function it_can_be_used()
    {
        $this->assertArrayHasKey(HasClient::class, (new ReflectionClass($this->trait))->getTraits());
    }

    #[Test]
    public function it_can_set_the_client()
    {
        $this->assertEquals($this->trait, $this->trait->setClient($this->client_mock));
    }

    #[Test]
    public function it_can_get_client()
    {
        $this->trait->setClient($this->client_mock);

        $this->assertEquals($this->client_mock, $this->trait->getClient());
    }

    #[Test]
    public function it_will_get_client_from_parent_if_it_does_not_have_one()
    {
        $this->assertEquals($this->trait->parent_client_mock, $this->trait->getClient());
    }

    #[Test]
    public function it_raises_exception_when_it_cannot_get_a_client()
    {
        $this->expectException(NoClientException::class);

        $this->trait->unsetParentModel();

        $this->trait->getClient();
    }
}
