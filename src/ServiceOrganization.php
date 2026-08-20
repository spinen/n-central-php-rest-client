<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Collection;
use Spinen\Ncentral\Support\Relations\HasMany;

/**
 * Class ServiceOrganization
 *
 * @property-read Collection|Customer[] $customers
 * @property-read Collection|Device[] $devices
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
        $related->setPath('/org-units/'.$this->orgUnitId.'/children');
        $related->parentModel = null;

        return $relation;
    }

    /**
     * Get all devices for this service organization
     */
    public function devices(): HasMany
    {
        $relation = $this->hasMany(Device::class);
        $related = $relation->getBuilder()->getModel();

        $related->setPath('/org-units/'.$this->orgUnitId.'/devices');
        $related->parentModel = null;

        return $relation;
    }
}
