<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Relations\BelongsTo;
use Spinen\Ncentral\Support\Relations\HasMany;

/**
 * Class Site
 *
 * @property-read Customer $customer
 * @property-read \Spinen\Ncentral\Support\Collection $devices
 */
class Site extends OrgUnit
{
    /**
     * Get the customer that owns this site
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'parentId');
    }

    /**
     * Get all devices for this site
     */
    public function devices(): HasMany
    {
        $relation = $this->hasMany(Device::class);
        $related = $relation->getBuilder()->getModel();

        // Override the path to use org-units instead of devices
        $related->setPath('/org-units/' . $this->orgUnitId . '/devices');
        $related->parentModel = null;

        return $relation;
    }
}
