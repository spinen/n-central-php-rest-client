<?php

namespace Tests\Unit\Concerns;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Crypt;
use Mockery;
use Mockery\Mock;
use ReflectionClass;
use Spinen\Ncentral\Api\Client as Ncentral;
use Spinen\Ncentral\Api\Token;
use Spinen\Ncentral\Concerns\HasNcentral;
use Spinen\Ncentral\Support\Builder;
use Tests\TestCase;
use Tests\Unit\Concerns\Stubs\User;

class HasNcentralTest extends TestCase
{
    /**
     * @var Mock
     */
    protected $builder_mock;

    /**
     * @var Mock
     */
    protected $client_mock;

    /**
     * @var Mock
     */
    protected $encrypter_mock;

    /**
     * @var User
     */
    protected $trait;

    protected function setUp(): void
    {
        $this->trait = new User();

        $this->client_mock = Mockery::mock(Ncentral::class);
        $this->client_mock->shouldReceive('setToken')
            ->withArgs(
                [
                    Mockery::any(),
                ]
            )
            ->andReturnSelf();

        $this->builder_mock = Mockery::mock(Builder::class);
        $this->builder_mock->shouldReceive('getClient')
            ->withNoArgs()
            ->andReturn($this->client_mock);
        $this->builder_mock->shouldReceive('setClient')
            ->withArgs(
                [
                    $this->client_mock,
                ]
            )
            ->andReturnSelf();

        Container::getInstance()
            ->instance(Builder::class, $this->builder_mock);

        Container::getInstance()
            ->instance(Ncentral::class, $this->client_mock);
    }

    /**
     * @test
     */
    public function it_can_be_used()
    {
        $this->assertArrayHasKey(HasNcentral::class, (new ReflectionClass($this->trait))->getTraits());
    }

    /**
     * @test
     */
    public function it_returns_a_builder_for_HALO_method()
    {
        $this->assertInstanceOf(Builder::class, $this->trait->ncentral());
    }

    /**
     * @test
     */
    public function it_caches_the_builder()
    {
        $this->assertNull($this->trait->getBuilder(), 'baseline');

        $this->trait->ncentral();

        $this->assertInstanceOf(Builder::class, $this->trait->getBuilder());
    }

    /**
     * @test
     */
    public function it_initializes_the_trait_as_expected()
    {
        $this->assertEmpty($this->trait->fillable, 'Baseline fillable');
        $this->assertEmpty($this->trait->hidden, 'Baseline hidden');

        $this->trait->initializeHasNcentral();

        $this->assertContains('ncentral_token', $this->trait->fillable, 'Fillable with ncentral_token');
        $this->assertContains('ncentral', $this->trait->hidden, 'Hide Ncentral');
        $this->assertContains('ncentral_token', $this->trait->hidden, 'Hide ncentral_token');
    }

    /**
     * @test
     */
    public function it_has_an_accessor_to_get_the_client()
    {
        $this->assertInstanceOf(Ncentral::class, $this->trait->getNcentralAttribute());
    }

    /**
     * @test
     */
    public function it_has_an_accessor_to_decrypt_ncentral_token()
    {
        Crypt::shouldReceive('decryptString')
            ->once()
            ->with($this->trait->attributes['ncentral_token'])
            ->andReturn(serialize(new Token(access_token: 'decrypted')));

        ($this->trait->ncentralToken()->get)(value: null, attributes: ['ncentral_token' => $this->trait->attributes['ncentral_token']]);
    }

    /**
     * @test
     */
    public function it_does_not_try_to_decrypt_null_ncentral_token()
    {
        $this->trait->attributes['ncentral_token'] = null;

        Crypt::shouldReceive('decryptString')
            ->never()
            ->withAnyArgs();

        $this->assertNull(($this->trait->ncentralToken()->get)(value: null, attributes: ['ncentral_token' => $this->trait->attributes['ncentral_token']]));
    }

    /**
     * @test
     */
    public function it_has_mutator_to_encrypt_ncentral_token()
    {
        Crypt::shouldReceive('encryptString')
            ->once()
            ->with(serialize('unencrypted'))
            ->andReturn();

        ($this->trait->ncentralToken()->set)('unencrypted');

        $this->assertEquals('encrypted', $this->trait->attributes['ncentral_token']);
    }

    /**
     * @test
     */
    public function it_does_not_mutate_a_null_ncentral_token()
    {
        Crypt::shouldReceive('encryptString')
            ->never()
            ->withAnyArgs();

        $this->assertNull(($this->trait->ncentralToken()->set)(null));
    }

    /**
     * @test
     */
    public function it_invalidates_builder_cache_when_setting_ncentral_token()
    {
        Crypt::shouldReceive('encryptString')
            ->withAnyArgs();

        // Force cache
        $this->trait->ncentral();

        $this->assertNotNull($this->trait->getBuilder(), 'Baseline that cache exist');

        ($this->trait->ncentralToken()->set)('changed');

        $this->assertNull($this->trait->getBuilder(), 'Cache was invalidated');
    }
}
