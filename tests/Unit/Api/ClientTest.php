<?php

namespace Tests\Unit\Api;

use Carbon\Carbon;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Str;
use Spinen\Ncentral\Api\Client;
use Spinen\Ncentral\Api\Token;
use Spinen\Ncentral\Exceptions\ClientConfigurationException;
use Tests\TestCase;
use TypeError;

/**
 * Class ClientTest
 */
class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(Client::class, new Client($this->configs));
    }

    /**
     * @test
     */
    public function it_expects_the_configs_argument_to_be_an_array()
    {
        $this->expectException(TypeError::class);

        new Client(configs: '');
    }

    /**
     * @test
     */
    public function it_raises_exception_without_an_url()
    {
        $this->expectException(ClientConfigurationException::class);

        unset($this->configs['url']);

        new Client($this->configs);
    }

    /**
     * @test
     */
    public function it_raises_exception_when_url_is_not_a_valid_url()
    {
        $this->expectException(ClientConfigurationException::class);

        $this->configs['url'] = 'invalid';

        new Client($this->configs);
    }

    /**
     * @test
     */
    public function it_expects_the_guzzle_argument_to_be_a_guzzle_if_provided()
    {
        $this->expectException(TypeError::class);

        new Client(configs: $this->configs, guzzle: '');
    }

    /**
     * @test
     */
    public function it_expects_the_token_argument_to_be_a_token_if_provided()
    {
        $this->expectException(TypeError::class);

        new Client(configs: $this->configs, token: '');
    }

    /**
     * @test
     */
    public function it_allows_setting_the_token_as_a_token()
    {
        $token = new Token(access_token: $access_token = Str::random());

        $client = (new Client($this->configs))
            ->setToken($token);

        $this->assertEquals($access_token, $client->getToken()->access_token);
    }

    /**
     * @test
     */
    public function it_allows_setting_the_token_as_a_string()
    {
        $client = (new Client($this->configs))
            ->setToken($access_token = Str::random());

        $this->assertEquals($access_token, $client->getToken()->access_token);
    }

    /**
     * @test
     */
    public function it_builds_correct_uri()
    {
        $client = new Client($this->configs);

        $this->assertEquals($this->configs['url'].'/', $client->uri(), 'slash on end of URL');
        $this->assertEquals($this->configs['url'].'/resource', $client->uri('resource'), 'simple URI');
        $this->assertEquals($this->configs['url'].'/resource', $client->uri('/resource'), 'no double slash');
        $this->assertEquals($this->configs['url'].'/resource/', $client->uri('resource/'), 'leaves end slash');
        $this->assertEquals(
            $this->configs['url'].'?parameter=value',
            $client->uri('?parameter=value'),
            'query string'
        );
        $this->assertEquals(
            'http://other/url/resource/',
            $client->uri('resource/', 'http://other/url/'),
            'url as second parameter'
        );
    }

    /**
     * @test
     */
    public function it_raises_exception_when_guzzle_error()
    {
        $this->expectException(GuzzleException::class);

        (new Client(
            configs: $this->configs,
            guzzle: new Guzzle([
                'handler' => HandlerStack::create(new MockHandler([
                    new RequestException(
                        'Bad request',
                        new Request('GET', $path = Str::random())
                    ),
                ])),
            ]),
        ))
            ->setToken(Str::random())
            ->request($path);
    }

    /**
     * @test
     *
     * @dataProvider tokenProvider
     */
    public function it_knows_if_a_token_is_valid($valid, $token = null)
    {
        $client = new Client($this->configs);

        if (! is_null($token)) {
            $client->setToken(is_callable($token) ? $token() : $token);
        }

        $this->assertEquals($valid, $client->validToken());
    }

    public static function tokenProvider()
    {
        return [
            'new client' => [
                'valid' => false,
                'token' => null,
            ],

            'new token' => [
                'valid' => true,
                'token' => new Token(access_token: Str::random()),
            ],

            'expired' => [
                'valid' => false,
                'token' => function () {
                    Carbon::setTestNow($now = Carbon::now()->subSeconds(2));
                    $token = new Token(access_token: Str::random(), expires_in: 1);
                    Carbon::setTestNow();

                    return $token;
                },
            ],
        ];
    }

    /**
     * @test
     */
    public function it_gets_cached_token_if_existing_token_valid()
    {
        $client = (new Client($this->configs))
            ->setToken($token = new Token(access_token: 'cached'));

        $this->assertEquals($token, $client->getToken());
    }
}
