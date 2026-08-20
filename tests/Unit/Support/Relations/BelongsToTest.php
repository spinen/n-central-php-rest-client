<?php

namespace Tests\Unit\Support\Relations;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Spinen\Ncentral\Support\Builder;
use Spinen\Ncentral\Support\Collection;
use Spinen\Ncentral\Support\Relations\BelongsTo;
use Tests\Unit\Support\Stubs\Model;

class BelongsToTest extends RelationCase
{
    protected BelongsTo $relation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder_mock->shouldReceive('whereId')
            ->withArgs(
                [
                    1,
                ]
            )
            ->andReturnSelf();

        $this->model_mock->shouldReceive('getAttribute')
            ->withArgs(
                [
                    'id',
                ]
            )
            ->andReturn(1);

        $this->relation = new BelongsTo($this->builder_mock, $this->model_mock, 'id');
    }

    #[Test]
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(BelongsTo::class, $this->relation);
    }

    #[Test]
    public function it_can_get_the_child()
    {
        $this->assertEquals($this->model_mock, $this->relation->getChild());
    }

    #[Test]
    public function it_can_get_the_value_of_the_foregin_key()
    {
        $this->assertEquals(1, $this->relation->getForeignKey());
    }

    #[Test]
    public function it_can_get_the_name_of_the_foreign_key()
    {
        $this->assertEquals('id', $this->relation->getForeignKeyName());
    }

    #[Test]
    public function it_gets_the_first_value_from_the_results_of_the_builder()
    {
        $results = [
            new Model(['name' => 'first']),
            new Model(['name' => 'second']),
        ];

        $this->builder_mock->shouldReceive('get')
            ->once()
            ->withNoArgs()
            ->andReturn(Collection::make($results));

        $results = $this->relation->getResults();

        $this->assertInstanceOf(Model::class, $results, 'Model instance');

        $this->assertEquals('first', $results->name, 'Correct one');
    }

    #[Test]
    public function it_returns_null_if_foreign_key_is_null()
    {
        $builder_mock = Mockery::mock(Builder::class);
        $builder_mock->shouldReceive('getModel')
            ->andReturn($this->parent_mock);
        $builder_mock->shouldReceive('whereId')
            ->withArgs(
                [
                    null,
                ]
            )
            ->andReturnSelf();

        $model_mock = Mockery::mock(Model::class);
        $model_mock->shouldReceive('getAttribute')
            ->withArgs(
                [
                    'id',
                ]
            )
            ->andReturn(null);

        $this->relation = new BelongsTo($builder_mock, $model_mock, 'id');

        $this->assertNull($this->relation->getResults());
    }
}
