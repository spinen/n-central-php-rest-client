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
        return $this->hasMany(Customer::class)
            ->withPath('/org-units/'.$this->orgUnitId.'/children');
    }
}
