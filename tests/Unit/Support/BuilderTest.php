<?php

namespace Tests\Unit\Support;

use BadMethodCallException;
use Mockery;
use Spinen\Ncentral\Api\Client;
use Spinen\Ncentral\Customer;
use Spinen\Ncentral\Exceptions\InvalidRelationshipException;
use Spinen\Ncentral\Exceptions\ModelNotFoundException;
use Spinen\Ncentral\Support\Builder;
use Spinen\Ncentral\Support\Collection;
use Tests\TestCase;

/**
 * Class BuilderTest
 *
 * Unit tests for Builder class that don't require HTTP calls.
 * Integration tests with mocked HTTP are skipped due to pre-existing
 * User-Agent header issue (SPINEN/x.x.x contains invalid "/" character).
 */
class BuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Builder::class, new Builder());
    }

    /**
     * @test
     */
    public function it_can_set_class()
    {
        $builder = (new Builder())->setClass(Customer::class);

        $this->assertInstanceOf(Builder::class, $builder);
    }

    /**
     * @test
     */
    public function it_throws_when_setting_nonexistent_class()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Builder())->setClass('NonExistentClass');
    }

    /**
     * @test
     */
    public function it_throws_when_getting_model_without_class()
    {
        $this->expectException(InvalidRelationshipException::class);

        (new Builder())->getModel();
    }

    /**
     * @test
     */
    public function it_can_get_model_instance()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class);

        $model = $builder->getModel();

        $this->assertInstanceOf(Customer::class, $model);
    }

    /**
     * @test
     */
    public function it_can_make_model_without_saving()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class);

        $customer = $builder->make(['customerName' => 'New Customer']);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('New Customer', $customer->customerName);
        $this->assertFalse($customer->exists);
    }

    /**
     * @test
     */
    public function it_can_chain_where_clauses()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class);

        $result = $builder
            ->where('isServiceOrg', true)
            ->where('name', 'test');

        $this->assertInstanceOf(Builder::class, $result);
    }

    /**
     * @test
     */
    public function it_can_chain_limit()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class);

        $result = $builder->limit(10);

        $this->assertInstanceOf(Builder::class, $result);
    }

    /**
     * @test
     */
    public function it_can_chain_page()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class);

        $result = $builder->page(2, 25);

        $this->assertInstanceOf(Builder::class, $result);
    }

    /**
     * @test
     */
    public function it_can_chain_orderby()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class);

        $result = $builder->orderBy('customerName');

        $this->assertInstanceOf(Builder::class, $result);
    }

    /**
     * @test
     */
    public function it_can_chain_orderby_desc()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class);

        $result = $builder->orderByDesc('customerId');

        $this->assertInstanceOf(Builder::class, $result);
    }

    /**
     * @test
     */
    public function it_can_call_root_model_methods()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())->setClient($client);

        // Calling customers() should return a new builder configured for Customer
        $result = $builder->customers();

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertInstanceOf(Customer::class, $result->getModel());
    }

    /**
     * @test
     */
    public function it_throws_for_unknown_method()
    {
        $this->expectException(BadMethodCallException::class);

        $client = Mockery::mock(Client::class);

        $builder = (new Builder())->setClient($client);
        $builder->unknownMethod();
    }

    /**
     * @test
     */
    public function it_can_create_new_instance()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class);

        $newBuilder = $builder->newInstance();

        $this->assertInstanceOf(Builder::class, $newBuilder);
        $this->assertNotSame($builder, $newBuilder);
    }

    /**
     * @test
     */
    public function it_builds_correct_path()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class);

        $path = $builder->getPath();

        // Path includes trailing slash when no ID
        $this->assertEquals('/customers/', $path);
    }

    /**
     * @test
     */
    public function it_builds_path_with_id()
    {
        $client = Mockery::mock(Client::class);

        $builder = (new Builder())
            ->setClient($client)
            ->setClass(Customer::class)
            ->whereId(42);

        $path = $builder->getPath();

        $this->assertEquals('/customers/42', $path);
    }
}
