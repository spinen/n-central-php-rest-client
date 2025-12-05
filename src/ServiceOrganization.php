<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;
use Spinen\Ncentral\Support\Relations\HasMany;

/**
 * Class ServiceOrganization
 *
 * @property int $orgUnitId
 * @property int|null $parentId
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
 * @property-read \Spinen\Ncentral\Support\Collection $customers
 */
class ServiceOrganization extends Model
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
