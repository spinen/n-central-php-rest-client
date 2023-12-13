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
