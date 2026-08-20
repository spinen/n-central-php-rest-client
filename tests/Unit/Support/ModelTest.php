<?php

namespace Tests\Unit\Support;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Spinen\Ncentral\Api\Client;
use Spinen\Ncentral\Exceptions\ModelReadonlyException;
use Spinen\Ncentral\Support\Collection;
use Tests\TestCase;
use Tests\Unit\Support\Stubs\Model;

class ModelTest extends TestCase
{
    protected $client_mock;

    protected Model $model;

    protected function setUp(): void
    {
        $this->client_mock = Mockery::mock(Client::class);
        $this->model = (new Model)->setClient($this->client_mock);
    }

    #[Test]
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Model::class, $this->model);
    }

    #[Test]
    public function it_can_fill_attributes()
    {
        $this->model->fill(['name' => 'test', 'value' => 123]);

        $this->assertEquals('test', $this->model->name);
        $this->assertEquals(123, $this->model->value);
    }

    #[Test]
    public function it_can_get_and_set_attributes_dynamically()
    {
        $this->model->foo = 'bar';

        $this->assertEquals('bar', $this->model->foo);
    }

    #[Test]
    public function it_can_check_if_attribute_isset()
    {
        $this->model->fill(['name' => 'test']);

        $this->assertTrue(isset($this->model->name));
        $this->assertFalse(isset($this->model->missing));
    }

    #[Test]
    public function it_can_unset_attribute()
    {
        $this->model->fill(['name' => 'test']);
        unset($this->model->name);

        $this->assertFalse(isset($this->model->name));
    }

    #[Test]
    public function it_can_convert_to_array()
    {
        $this->model->fill(['name' => 'test', 'value' => 123]);

        $array = $this->model->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('test', $array['name']);
    }

    #[Test]
    public function it_can_convert_to_json()
    {
        $this->model->fill(['name' => 'test']);

        $json = $this->model->toJson();

        $this->assertJson($json);
        $this->assertStringContainsString('test', $json);
    }

    #[Test]
    public function it_can_convert_to_string()
    {
        $this->model->fill(['name' => 'test']);

        $this->assertStringContainsString('test', (string) $this->model);
    }

    #[Test]
    public function it_implements_array_access()
    {
        $this->model['name'] = 'test';

        $this->assertTrue(isset($this->model['name']));
        $this->assertEquals('test', $this->model['name']);

        unset($this->model['name']);
        $this->assertFalse(isset($this->model['name']));
    }

    #[Test]
    public function it_can_get_primary_key_name()
    {
        $this->assertEquals('id', $this->model->getKeyName());
    }

    #[Test]
    public function it_can_get_primary_key_value()
    {
        $this->model->fill(['id' => 42]);

        $this->assertEquals(42, $this->model->getKey());
    }

    #[Test]
    public function it_can_get_path_without_id()
    {
        $this->assertEquals('/test', $this->model->getPath());
    }

    #[Test]
    public function it_can_get_path_with_id_when_exists()
    {
        $this->model->fill(['id' => 42]);
        $this->model->exists = true;

        $this->assertEquals('/test/42', $this->model->getPath());
    }

    #[Test]
    public function it_can_get_path_with_extra()
    {
        $this->model->setExtra('details');

        $this->assertEquals('/test/details', $this->model->getPath());
    }

    #[Test]
    public function it_can_get_path_with_query_params()
    {
        $this->assertEquals('/test?foo=bar', $this->model->getPath(null, ['foo' => 'bar']));
    }

    #[Test]
    public function it_converts_bool_to_string_in_query()
    {
        $path = $this->model->getPath(null, ['active' => true, 'deleted' => false]);

        $this->assertStringContainsString('active=true', $path);
        $this->assertStringContainsString('deleted=false', $path);
    }

    #[Test]
    public function it_can_set_and_get_readonly()
    {
        $this->assertFalse($this->model->getReadonlyModel());

        $this->model->setReadonly(true);

        $this->assertTrue($this->model->getReadonlyModel());
    }

    #[Test]
    public function it_throws_exception_when_setting_attribute_on_readonly_model()
    {
        $this->expectException(ModelReadonlyException::class);

        $this->model->setReadonly(true);
        $this->model->name = 'test';
    }

    #[Test]
    public function it_can_create_new_instance()
    {
        $new = $this->model->newInstance(['name' => 'new']);

        $this->assertInstanceOf(Model::class, $new);
        $this->assertEquals('new', $new->name);
        $this->assertFalse($new->exists);
    }

    #[Test]
    public function it_can_create_from_builder()
    {
        $new = $this->model->newFromBuilder(['name' => 'existing', 'id' => 1]);

        $this->assertInstanceOf(Model::class, $new);
        $this->assertEquals('existing', $new->name);
        $this->assertTrue($new->exists);
    }

    #[Test]
    public function it_can_get_response_key()
    {
        $this->assertEquals('data', $this->model->getResponseKey());
    }

    #[Test]
    public function it_can_peel_wrapper_property()
    {
        $wrapped = ['data' => ['id' => 1, 'name' => 'test']];

        $peeled = $this->model->peelWrapperPropertyIfNeeded($wrapped);

        $this->assertEquals(['id' => 1, 'name' => 'test'], $peeled);
    }

    #[Test]
    public function it_returns_original_if_no_wrapper()
    {
        $unwrapped = ['id' => 1, 'name' => 'test'];

        $result = $this->model->peelWrapperPropertyIfNeeded($unwrapped);

        $this->assertEquals($unwrapped, $result);
    }

    #[Test]
    public function it_can_cast_given_many_to_collection()
    {
        $items = [
            ['id' => 1, 'name' => 'first'],
            ['id' => 2, 'name' => 'second'],
        ];

        $collection = $this->model->givenMany(Model::class, $items);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(Model::class, $collection->first());
    }

    #[Test]
    public function it_can_cast_given_one_to_model()
    {
        $attributes = ['id' => 1, 'name' => 'single'];

        $result = $this->model->givenOne(Model::class, $attributes);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals('single', $result->name);
    }

    #[Test]
    public function it_can_set_and_check_relations()
    {
        $this->assertFalse($this->model->relationLoaded('items'));

        $this->model->setRelation('items', new Collection);

        $this->assertTrue($this->model->relationLoaded('items'));
    }

    #[Test]
    public function it_can_get_order_by_parameter()
    {
        $this->assertEquals('order', $this->model->getOrderByParameter());
        $this->assertEquals('orderdesc', $this->model->getOrderByDirectionParameter());
    }

    #[Test]
    public function it_can_set_nested()
    {
        $this->assertFalse($this->model->isNested());

        $this->model->setNested(true);

        $this->assertTrue($this->model->isNested());
    }

    #[Test]
    public function it_can_get_default_wheres()
    {
        $result = $this->model->getDefaultWheres(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $result);
    }

    #[Test]
    public function it_can_delete()
    {
        $this->model->fill(['id' => 42]);
        $this->model->exists = true;

        $this->client_mock->shouldReceive('delete')
            ->once()
            ->with('/test/42')
            ->andReturn([]);

        $this->assertTrue($this->model->delete());
    }

    #[Test]
    public function it_returns_false_when_deleting_readonly()
    {
        $this->model->setReadonly(true);

        $this->assertFalse($this->model->delete());
    }

    #[Test]
    public function it_can_save()
    {
        $this->model->fill(['name' => 'new']);

        $this->client_mock->shouldReceive('post')
            ->once()
            ->andReturn(['data' => ['id' => 1, 'name' => 'new']]);

        $this->assertTrue($this->model->save());
        $this->assertTrue($this->model->exists);
        $this->assertTrue($this->model->wasRecentlyCreated);
    }

    #[Test]
    public function it_returns_true_when_saving_unchanged()
    {
        // Model with no changes (synced original)
        $model = $this->model->newFromBuilder(['id' => 1, 'name' => 'test']);

        $this->assertTrue($model->save());
    }

    #[Test]
    public function it_returns_false_when_saving_readonly()
    {
        $this->model->setReadonly(true);
        $this->model->fill(['name' => 'test']);

        $this->assertFalse($this->model->save());
    }
}
