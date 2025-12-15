<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Relations\BelongsTo;
use Spinen\Ncentral\Support\Relations\HasMany;

/**
 * Class Customer
 *
 * @property int $customerId
 * @property string $customerName
 * @property bool $isServiceOrg
 * @property bool $isSystem
 * @property-read ServiceOrganization $serviceOrganization
 * @property-read \Spinen\Ncentral\Support\Collection $sites
 * @property-read \Spinen\Ncentral\Support\Collection $devices
 */
class Customer extends OrgUnit
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'orgUnitId' => 'int',
        'customerId' => 'int',
        'parentId' => 'int',
        'isSystem' => 'bool',
        'isServiceOrg' => 'bool',
    ];

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'customerId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/customers';

    /**
     * Get the service organization that owns this customer
     */
    public function serviceOrganization(): BelongsTo
    {
        return $this->belongsTo(ServiceOrganization::class, 'parentId');
    }

    /**
     * Get all sites for this customer
     */
    public function sites(): HasMany
    {
        $relation = $this->hasMany(Site::class);
        $related = $relation->getBuilder()->getModel();

        // Get children of this org unit
        $related->setPath('/org-units/' . $this->customerId . '/children');
        $related->parentModel = null;

        return $relation;
    }

    /**
     * Get all devices for this customer
     */
    public function devices(): HasMany
    {
        $relation = $this->hasMany(Device::class);
        $related = $relation->getBuilder()->getModel();

        // Override the path to use org-units instead of customers
        $related->setPath('/org-units/' . $this->customerId . '/devices');
        $related->parentModel = null;

        return $relation;
    }
}
