<?php

namespace Spinen\Ncentral\Api;

use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;
use RuntimeException;
use Spinen\Ncentral\Exceptions\ApiException;
use Spinen\Ncentral\Exceptions\ClientConfigurationException;
use Spinen\Version\Version;

/**
 * Class Client
 */
class Client
{
    /**
     * Client constructor.
     */
    public function __construct(
        protected array $configs,
        protected Guzzle $guzzle = new Guzzle,
        protected Token $token = new Token,
        protected bool $debug = false,
    ) {
        $this->setConfigs($configs);
        $this->setToken($token);
    }

    /**
     * Shortcut to 'DELETE' request
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function delete(string $path): ?array
    {
        return $this->request($path, [], 'DELETE');
    }

    /**
     * Shortcut to 'GET' request
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function get(string $path): ?array
    {
        return $this->request($path, [], 'GET');
    }

    /**
     * Get, return, or refresh the token
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function getToken(): Token
    {
        return match (true) {
            $this->token->isValid() => $this->token,
            // TODO: Verify that token can be refreshed
            $this->token->needsRefreshing() => $this->token = $this->refreshToken(),
            default => $this->token = $this->requestToken(),
        };
    }

    public function getVersion()
    {
        return new Version(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'VERSION');
    }

    /**
     * Process exception
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    protected function processException(GuzzleException $e): void
    {
        if (! is_a($e, RequestException::class)) {
            throw $e;
        }

        /** @var RequestException $e */
        $body = $e->getResponse()->getBody()->getContents();

        $results = json_decode($body, true);

        throw new ApiException(
            body: $body,
            code: $results['status'],
            message: $results['message'],
            previous: $e,
        );
    }

    /**
     * Shortcut to 'POST' request
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function post(string $path, array $data): ?array
    {
        return $this->request($path, $data, 'POST');
    }

    /**
     * Shortcut to 'PUT' request
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    // TODO: Enable this once they add endpoints that support put
    // public function put(string $path, array $data): ?array
    // {
    //     return $this->request($path, $data, 'PUT');
    // }

    /**
     * Make an API call to Ncentral
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function request(?string $path, ?array $data = [], ?string $method = 'GET'): ?array
    {
        // TODO: Decide if going to do more than let the exception bubble up
        // try {
            return json_decode(
                associative: true,
                json: $this->guzzle->request(
                    method: $method,
                    options: [
                        'debug' => $this->debug,
                        'headers' => [
                            'Authorization' => (string) $this->getToken(),
                            'Content-Type' => 'application/json',
                            'User-Agent' => 'SPINEN/'.$this->getVersion(),
                        ],
                        'body' => empty($data) ? null : json_encode($data),
                    ],
                    uri: $this->uri($path),
                )
                    ->getBody()
                    ->getContents(),
            );
        // } catch (GuzzleException $e) {
        //     $this->processException($e);
        // }
    }

    // TODO: Cleanup/combine the refresh & request methods

    /**
     * Refresh a token
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function refreshToken(): Token
    {
        // TODO: Decide if going to do more than let the exception bubble up
        // try {
            $this->token = new Token(...json_decode(
                associative: true,
                json: $this->guzzle->request(
                    method: 'POST',
                    options: [
                        'debug' => $this->debug,
                        'headers' => [
                            'Accept' => '*/*',
                            'Authorization' => 'Bearer'.' '.$this->configs['jwt'],
                            'Content-Type' => 'text/plain',
                            'User-Agent' => 'SPINEN/'.$this->getVersion(),
                            'X-ACCESS-EXPIRY-OVERRIDE' => $this->configs['override']['access'].'s',
                            'X-REFRESH-EXPIRY-OVERRIDE' => $this->configs['override']['refresh'].'s',
                        ],
                        'body' => $this->token->refresh_token,
                    ],
                    uri: $this->uri('auth/refresh'),
                )
                    ->getBody()
                    ->getContents(),
            )['tokens']);

            return $this->token;
        // } catch (GuzzleException $e) {
        //     $this->processException($e);
        // }
    }

    /**
     * Request a token
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws RuntimeException
     */
    public function requestToken(): Token
    {
        // TODO: Decide if going to do more than let the exception bubble up
        // try {
            $this->token = new Token(...json_decode(
                associative: true,
                json: $this->guzzle->request(
                    method: 'POST',
                    options: [
                        'debug' => $this->debug,
                        'headers' => [
                            'Accept' => '*/*',
                            'Authorization' => 'Bearer'.' '.$this->configs['jwt'],
                            'Content-Type' => 'text/plain',
                            'User-Agent' => 'SPINEN/'.$this->getVersion(),
                            'X-ACCESS-EXPIRY-OVERRIDE' => $this->configs['override']['access'].'s',
                            'X-REFRESH-EXPIRY-OVERRIDE' => $this->configs['override']['refresh'].'s',
                        ],
                    ],
                    uri: $this->uri('auth/authenticate'),
                )
                    ->getBody()
                    ->getContents(),
            )['tokens']);

            return $this->token;
        // } catch (GuzzleException $e) {
        //     $this->processException($e);
        // }
    }

    /**
     * Validate & set the configs
     *
     * @throws ClientConfigurationException
     */
    protected function setConfigs(array $configs): self
    {
        // Replace empty strings with nulls in config values
        $this->configs = array_map(fn ($v) => $v === '' ? null : $v, $configs);

        // Default if not set
        $this->configs['override']['access'] ??= 3600;
        $this->configs['override']['refresh'] ??= 90000;

        if (! is_numeric($this->configs['override']['access'])) {
            throw new ClientConfigurationException('The "access override" must be an int');
        }

        if ($this->configs['override']['access'] > 3600) {
            throw new ClientConfigurationException('The "access override" must be less than an or equal 3600');
        }

        if (! is_numeric($this->configs['override']['refresh'])) {
            throw new ClientConfigurationException('The "refresh override" must be an int');
        }

        if ($this->configs['override']['refresh'] > 90000) {
            throw new ClientConfigurationException('The "refresh override" must be less than an or equal 90000');
        }

        if (is_null($this->configs['jwt'] ?? null)) {
            throw new ClientConfigurationException('The "jwt" cannot be null');
        }

        if (is_null($this->configs['url'] ?? null)) {
            throw new ClientConfigurationException('The "url" cannot be null');
        }

        if (! filter_var($this->configs['url'], FILTER_VALIDATE_URL)) {
            throw new ClientConfigurationException(
                sprintf('A valid url must be provided for "url" [%s]', $this->configs['url'])
            );
        }

        return $this;
    }

    /**
     * Set debug
     */
    public function setDebug(bool $debug = true): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Set the token & refresh if needed
     */
    public function setToken(Token|string $token): self
    {
        $this->token = is_string($token)
            ? new Token(access_token: $token)
            : $token;

        return $this;
    }

    /**
     * URL to Ncentral
     *
     * If path is passed in, then append it to the end. By default, it will use the url
     * in the configs, but if a url is passed in as a second parameter then it is used.
     * If no url is found it will use the hard-coded v2 Ncentral API URL.
     */
    public function uri(?string $path = null, ?string $url = null): string
    {
        if ($path && Str::startsWith($path, 'http')) {
            return $path;
        }

        $path = ltrim($path ?? '/', '/');

        return rtrim($url ?? $this->configs['url'], '/')
            .($path ? (Str::startsWith($path, '?') ? null : '/').$path : '/');
    }

    /**
     * Is the token valid
     */
    public function validToken(): bool
    {
        return $this->token->isValid();
    }
}
