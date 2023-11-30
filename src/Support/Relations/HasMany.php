<?php

namespace Spinen\Ncentral\Support\Relations;

use GuzzleHttp\Exception\GuzzleException;
use Spinen\Ncentral\Exceptions\InvalidRelationshipException;
use Spinen\Ncentral\Exceptions\NoClientException;
use Spinen\Ncentral\Exceptions\TokenException;
use Spinen\Ncentral\Support\Collection;

/**
 * Class HasMany
 */
class HasMany extends Relation
{
    /**
     * Get the results of the relationship.
     *
     * @throws GuzzleException
     * @throws InvalidRelationshipException
     * @throws NoClientException
     * @throws TokenException
     */
    public function getResults(): Collection
    {
        return $this->getBuilder()
            ->get();
    }
}
