<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Abstract Class OrgUnit
 *
 * Base class for organizational units (ServiceOrganization, Customer, Site)
 * containing shared properties and functionality.
 *
 * @property string $contactFirstName
 * @property string $contactLastName
 * @property int $orgUnitId
 * @property string $orgUnitName
 * @property string $orgUnitType
 * @property ?string $city
 * @property ?string $contactDepartment
 * @property ?string $contactEmail
 * @property ?string $contactPhone
 * @property ?string $contactPhoneExt
 * @property ?string $contactTitle
 * @property ?string $country
 * @property ?string $county
 * @property ?string $externalId
 * @property ?string $externalId2
 * @property ?int $parentId
 * @property ?string $phone
 * @property ?string $postalCode
 * @property ?string $stateProv
 * @property ?string $street1
 * @property ?string $street2
 */
abstract class OrgUnit extends Model
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
}
