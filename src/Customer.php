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
        return $this->hasMany(Site::class)
            ->withPath('/org-units/'.$this->customerId.'/children');
    }

    /**
     * Get all devices for this customer
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class)
            ->withPath('/org-units/'.$this->customerId.'/devices');
    }
}
