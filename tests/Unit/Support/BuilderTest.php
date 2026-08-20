<?php

namespace Tests\Unit\Support;

use BadMethodCallException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Spinen\Ncentral\Api\Client;
use Spinen\Ncentral\Device;
use Spinen\Ncentral\Exceptions\InvalidRelationshipException;
use Spinen\Ncentral\Exceptions\ModelNotFoundException;
use Spinen\Ncentral\Support\Builder;
use Spinen\Ncentral\Support\Collection;
use Tests\TestCase;
use Tests\Unit\Support\Stubs\Model;

class BuilderTest extends TestCase
{
    protected $client_mock;

    protected Builder $builder;

    protected function setUp(): void
    {
        $this->client_mock = Mockery::mock(Client::class);
        $this->builder = (new Builder)->setClient($this->client_mock);
    }

    #[Test]
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Builder::class, $this->builder);
    }

    #[Test]
    public function it_can_set_class()
    {
        $this->builder->setClass(Model::class);

        $this->assertInstanceOf(Model::class, $this->builder->getModel());
    }

    #[Test]
    public function it_throws_exception_for_invalid_class()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->builder->setClass('NonExistentClass');
    }

    #[Test]
    public function it_throws_exception_when_getting_model_without_class()
    {
        $this->expectException(InvalidRelationshipException::class);

        $this->builder->getModel();
    }

    #[Test]
    public function it_can_add_where_clause()
    {
        $this->builder->setClass(Model::class);

        $result = $this->builder->where('status', 'active');

        $this->assertSame($this->builder, $result);
        $this->assertStringContainsString('status=active', $this->builder->getPath());
    }

    #[Test]
    public function it_can_add_where_id()
    {
        $this->builder->setClass(Model::class);

        $this->builder->whereId(42);

        $this->assertStringContainsString('/42', $this->builder->getPath());
    }

    #[Test]
    public function it_can_add_where_not()
    {
        $this->builder->setClass(Model::class);

        $this->builder->whereNot('deleted');

        $this->assertStringContainsString('deleted=false', $this->builder->getPath());
    }

    #[Test]
    public function it_can_set_limit()
    {
        $this->builder->setClass(Model::class);

        $this->builder->limit(10);

        $this->assertStringContainsString('count=10', $this->builder->getPath());
    }

    #[Test]
    public function it_can_use_take_as_alias_for_limit()
    {
        $this->builder->setClass(Model::class);

        $this->builder->take(5);

        $this->assertStringContainsString('count=5', $this->builder->getPath());
    }

    #[Test]
    public function it_can_set_page()
    {
        $this->builder->setClass(Model::class);

        $this->builder->page(2);

        $this->assertStringContainsString('pageNumber=2', $this->builder->getPath());
    }

    #[Test]
    public function it_can_set_page_with_size()
    {
        $this->builder->setClass(Model::class);

        $this->builder->page(2, 25);

        $path = $this->builder->getPath();
        $this->assertStringContainsString('pageNumber=2', $path);
        $this->assertStringContainsString('pageSize=25', $path);
    }

    #[Test]
    public function it_can_paginate()
    {
        $this->builder->setClass(Model::class);

        $this->builder->paginate(50);

        $path = $this->builder->getPath();
        $this->assertStringContainsString('paginate=true', $path);
        $this->assertStringContainsString('pageSize=50', $path);
    }

    #[Test]
    public function it_can_disable_pagination()
    {
        $this->builder->setClass(Model::class);

        $this->builder->paginate(null);

        $this->assertStringContainsString('paginate=false', $this->builder->getPath());
    }

    #[Test]
    public function it_can_order_by()
    {
        $this->builder->setClass(Model::class);

        $this->builder->orderBy('name');

        $path = $this->builder->getPath();
        $this->assertStringContainsString('order=name', $path);
    }

    #[Test]
    public function it_can_order_by_desc()
    {
        $this->builder->setClass(Model::class);

        $this->builder->orderByDesc('created');

        $path = $this->builder->getPath();
        $this->assertStringContainsString('order=created', $path);
        $this->assertStringContainsString('orderdesc=true', $path);
    }

    #[Test]
    public function it_can_select_fields()
    {
        $this->builder->setClass(Model::class);

        $this->builder->select(['id', 'name']);

        $this->assertStringContainsString('select=id%2Cname', $this->builder->getPath());
    }

    #[Test]
    public function it_can_enable_debug()
    {
        $result = $this->builder->debug(true);

        $this->assertSame($this->builder, $result);
    }

    #[Test]
    public function it_can_set_parent()
    {
        $parent = (new Model(['id' => 1]))->setClient($this->client_mock);
        $parent->exists = true;

        $result = $this->builder->setParent($parent);

        $this->assertSame($this->builder, $result);
    }

    #[Test]
    public function it_can_create_new_instance()
    {
        $this->builder->setClass(Model::class);

        $new = $this->builder->newInstance();

        $this->assertInstanceOf(Builder::class, $new);
        $this->assertNotSame($this->builder, $new);
    }

    #[Test]
    public function it_can_create_new_instance_for_model()
    {
        $new = $this->builder->newInstanceForModel(Model::class);

        $this->assertInstanceOf(Builder::class, $new);
        $this->assertInstanceOf(Model::class, $new->getModel());
    }

    #[Test]
    public function it_can_make_model_without_saving()
    {
        $this->builder->setClass(Model::class);

        $model = $this->builder->make(['name' => 'test']);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals('test', $model->name);
        $this->assertFalse($model->exists);
    }

    #[Test]
    public function it_throws_when_making_readonly_model()
    {
        $this->expectException(\RuntimeException::class);

        $this->builder->setClass(Model::class);
        $this->builder->getModel()->setReadonly(true);

        $this->builder->make(['name' => 'test']);
    }

    #[Test]
    public function it_can_get_results()
    {
        $this->builder->setClass(Model::class);

        $this->client_mock->shouldReceive('setDebug')->andReturnSelf();
        $this->client_mock->shouldReceive('request')
            ->once()
            ->andReturn([
                'data' => [
                    ['id' => 1, 'name' => 'first'],
                    ['id' => 2, 'name' => 'second'],
                ],
            ]);

        $results = $this->builder->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(2, $results);
    }

    #[Test]
    public function it_can_find_by_id()
    {
        $this->builder->setClass(Model::class);

        $this->client_mock->shouldReceive('setDebug')->andReturnSelf();
        $this->client_mock->shouldReceive('request')
            ->once()
            ->andReturn(['data' => ['id' => 42, 'name' => 'found']]);

        $result = $this->builder->find(42);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals(42, $result->id);
    }

    #[Test]
    public function it_can_create_and_save()
    {
        $this->builder->setClass(Model::class);

        $this->client_mock->shouldReceive('post')
            ->once()
            ->andReturn(['data' => ['id' => 1, 'name' => 'created']]);

        $model = $this->builder->create(['name' => 'created']);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertTrue($model->exists);
    }

    #[Test]
    public function it_can_call_root_model_methods()
    {
        $result = $this->builder->devices();

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertInstanceOf(Device::class, $result->getModel());
    }

    #[Test]
    public function it_throws_for_unknown_method()
    {
        $this->expectException(BadMethodCallException::class);

        $this->builder->unknownMethod();
    }

    #[Test]
    public function it_can_access_root_models_as_properties()
    {
        $this->client_mock->shouldReceive('setDebug')->andReturnSelf();
        $this->client_mock->shouldReceive('request')
            ->once()
            ->andReturn(['data' => [['deviceId' => 1]]]);

        $result = $this->builder->devices;

        $this->assertInstanceOf(Collection::class, $result);
    }

    #[Test]
    public function it_returns_null_for_unknown_property()
    {
        $this->assertNull($this->builder->unknownProperty);
    }
}
