<?php

namespace Spinen\Ncentral\Support\Relations;

use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use Spinen\Ncentral\Exceptions\ApiException;
use Spinen\Ncentral\Exceptions\InvalidRelationshipException;
use Spinen\Ncentral\Exceptions\NoClientException;
use Spinen\Ncentral\Support\Collection;

/**
 * Class HasMany
 */
class HasMany extends Relation
{
    /**
     * Override the path for the related model and detach from parent.
     *
     * Use this when the API endpoint differs from the default nested path.
     */
    public function withPath(string $path): self
    {
        $this->getRelated()->setPath($path);
        $this->getRelated()->parentModel = null;

        return $this;
    }

    /**
     * Get the results of the relationship.
     *
     * @throws ApiException
     * @throws GuzzleException
     * @throws InvalidRelationshipException
     * @throws NoClientException
     * @throws RuntimeException
     */
    public function getResults(): Collection
    {
        return $this->getBuilder()
            ->get();
    }
}
