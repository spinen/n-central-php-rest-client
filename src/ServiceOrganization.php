<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Relations\HasMany;

/**
 * Class ServiceOrganization
 *
 * @property-read \Spinen\Ncentral\Support\Collection $customers
 */
class ServiceOrganization extends OrgUnit
{
    /**
     * Get all customers for this service organization
     */
    public function customers(): HasMany
    {
        $relation = $this->hasMany(Customer::class);
        $related = $relation->getBuilder()->getModel();

        // Get children of this org unit
        $related->setPath('/org-units/' . $this->orgUnitId . '/children');
        $related->parentModel = null;

        return $relation;
    }
}
