<?php

namespace Tests\Unit\Support;

use Mockery;
use Spinen\Ncentral\Api\Client;
use Spinen\Ncentral\Customer;
use Spinen\Ncentral\Device;
use Spinen\Ncentral\Exceptions\ModelReadonlyException;
use Tests\TestCase;

/**
 * Class ModelTest
 *
 * Unit tests for Model class that don't require HTTP calls.
 * Integration tests with mocked HTTP are skipped due to pre-existing
 * User-Agent header issue (SPINEN/x.x.x contains invalid "/" character).
 */
class ModelTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $customer = new Customer();
        $this->assertInstanceOf(Customer::class, $customer);
    }

    /**
     * @test
     */
    public function it_can_fill_attributes()
    {
        $customer = new Customer(['customerId' => 123, 'customerName' => 'Test']);

        $this->assertEquals(123, $customer->customerId);
        $this->assertEquals('Test', $customer->customerName);
    }

    /**
     * @test
     */
    public function it_returns_true_when_saving_clean_model()
    {
        $client = Mockery::mock(Client::class);

        // Use Device which is not readonly by default
        $device = new Device();
        $device->setClient($client);
        $device->syncOriginal();

        // Model is not dirty, should return true without making API call
        $this->assertFalse($device->isDirty());
        $result = $device->save();

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_returns_false_when_saving_readonly_model()
    {
        $client = Mockery::mock(Client::class);

        $customer = (new Customer())
            ->setClient($client)
            ->setReadonly(true)
            ->fill(['customerName' => 'Test']);

        $result = $customer->save();

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_returns_false_when_deleting_readonly_model()
    {
        $client = Mockery::mock(Client::class);

        $customer = (new Customer(['customerId' => 123]))
            ->setClient($client)
            ->setReadonly(true);
        $customer->exists = true;

        $result = $customer->delete();

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_throws_when_setting_attribute_on_readonly()
    {
        $this->expectException(ModelReadonlyException::class);

        $customer = (new Customer())
            ->setReadonly(true);

        $customer->customerName = 'Test';
    }

    /**
     * @test
     */
    public function it_builds_correct_path()
    {
        $customer = new Customer(['customerId' => 42]);
        $customer->exists = true;

        $this->assertEquals('/customers/42', $customer->getPath());
    }

    /**
     * @test
     */
    public function it_builds_path_without_id_when_not_exists()
    {
        $customer = new Customer(['customerId' => 42]);
        $customer->exists = false;

        $this->assertEquals('/customers', $customer->getPath());
    }

    /**
     * @test
     */
    public function it_builds_path_with_query_params()
    {
        $customer = new Customer();

        $path = $customer->getPath(null, ['filter' => 'value']);

        $this->assertStringContainsString('filter=value', $path);
    }

    /**
     * @test
     */
    public function it_builds_path_with_extra()
    {
        $customer = new Customer(['customerId' => 1]);
        $customer->exists = true;

        $path = $customer->getPath('details');

        $this->assertEquals('/customers/1/details', $path);
    }

    /**
     * @test
     */
    public function it_converts_to_array()
    {
        $customer = new Customer(['customerId' => 1, 'customerName' => 'Test']);

        $array = $customer->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(1, $array['customerId']);
        $this->assertEquals('Test', $array['customerName']);
    }

    /**
     * @test
     */
    public function it_converts_to_json()
    {
        $customer = new Customer(['customerId' => 1, 'customerName' => 'Test']);

        $json = $customer->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(1, $decoded['customerId']);
    }

    /**
     * @test
     */
    public function it_converts_to_string_as_json()
    {
        $customer = new Customer(['customerId' => 1]);

        $string = (string) $customer;

        $this->assertJson($string);
    }

    /**
     * @test
     */
    public function it_tracks_dirty_attributes()
    {
        $customer = new Customer();
        $customer->syncOriginal();

        $this->assertFalse($customer->isDirty());

        // Use fill instead of direct assignment to avoid readonly check
        $customer->fill(['customerName' => 'New Name']);

        $this->assertTrue($customer->isDirty());
        $this->assertTrue($customer->isDirty('customerName'));
    }

    /**
     * @test
     */
    public function it_can_create_new_instance()
    {
        $client = Mockery::mock(Client::class);

        $original = (new Customer(['customerId' => 1]))
            ->setClient($client);

        $new = $original->newInstance(['customerId' => 2]);

        $this->assertInstanceOf(Customer::class, $new);
        $this->assertEquals(2, $new->customerId);
        $this->assertFalse($new->exists);
    }

    /**
     * @test
     */
    public function it_can_create_from_builder()
    {
        $client = Mockery::mock(Client::class);

        $customer = (new Customer())
            ->setClient($client);

        $built = $customer->newFromBuilder(['customerId' => 99, 'customerName' => 'Built']);

        $this->assertInstanceOf(Customer::class, $built);
        $this->assertEquals(99, $built->customerId);
        $this->assertTrue($built->exists);
    }

    /**
     * @test
     */
    public function it_implements_array_access()
    {
        $customer = new Customer(['customerId' => 1, 'customerName' => 'Test']);

        $this->assertTrue(isset($customer['customerId']));
        $this->assertEquals(1, $customer['customerId']);
        $this->assertEquals('Test', $customer['customerName']);
    }

    /**
     * @test
     */
    public function it_can_get_key()
    {
        $customer = new Customer(['customerId' => 42]);

        $this->assertEquals(42, $customer->getKey());
    }

    /**
     * @test
     */
    public function it_can_get_key_name()
    {
        $customer = new Customer();

        $this->assertEquals('customerId', $customer->getKeyName());
    }

    /**
     * @test
     */
    public function it_can_set_and_check_readonly()
    {
        $customer = new Customer();

        // Set readonly
        $customer->setReadonly(true);
        $this->assertTrue($customer->getReadonlyModel());

        // Unset readonly
        $customer->setReadonly(false);
        $this->assertFalse($customer->getReadonlyModel());
    }

    /**
     * @test
     */
    public function it_can_set_path()
    {
        $customer = new Customer();

        $customer->setPath('/custom-path');

        $this->assertEquals('/custom-path', $customer->getPath());
    }

    /**
     * @test
     */
    public function it_can_set_extra()
    {
        $customer = new Customer();

        $customer->setExtra('extra-path');

        $this->assertEquals('extra-path', $customer->getExtra());
    }
}
