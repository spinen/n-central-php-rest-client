<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;
use Spinen\Ncentral\Support\Relations\BelongsTo;
use Spinen\Ncentral\Support\Relations\HasMany;

/**
 * Class Site
 *
 * @property int $orgUnitId
 * @property int $parentId
 * @property string $orgUnitName
 * @property string $orgUnitType
 * @property string|null $externalId
 * @property string|null $externalId2
 * @property string $contactFirstName
 * @property string $contactLastName
 * @property string|null $phone
 * @property string|null $contactTitle
 * @property string|null $contactEmail
 * @property string|null $contactPhone
 * @property string|null $contactPhoneExt
 * @property string|null $contactDepartment
 * @property string|null $street1
 * @property string|null $street2
 * @property string|null $city
 * @property string|null $stateProv
 * @property string|null $country
 * @property string|null $postalCode
 * @property-read Customer $customer
 * @property-read \Spinen\Ncentral\Support\Collection $devices
 */
class Site extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'orgUnitId' => 'int',
        'parentId' => 'int',
    ];

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'orgUnitId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/org-units';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;

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
