<?php

namespace Spinen\Ncentral\Concerns;

use Spinen\Ncentral\Api\Client;
use Spinen\Ncentral\Exceptions\NoClientException;

trait HasClient
{
    /**
     * Client instance
     */
    protected Client $client;

    /**
     * Get the Client instance
     *
     * If there is no client assigned on the model, but it has a parent, then try to get the parent's client
     *
     * @throws NoClientException
     */
    public function getClient(): Client
    {
        if (! isset($this->client) && $this->parentModel) {
            $this->client = $this->parentModel->getClient();
        }

        if (isset($this->client)) {
            return $this->client;
        }

        throw new NoClientException();
    }

    /**
     * Set the client instance
     */
    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
