<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Collection;
use Spinen\Ncentral\Support\Model;
use Spinen\Ncentral\Support\Relations\HasMany;

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
 * @property-read Collection|OrgUnitCustomProperty[] $customProperties
 * @property-read Collection|ActiveIssue[] $activeIssues
 * @property-read Collection|JobStatus[] $jobStatuses
 * @property-read Collection|UserRole[] $userRoles
 * @property-read Collection|OrgUnitLimit[] $limits
 * @property-read Collection|User[] $users
 * @property-read Collection|AccessGroup[] $accessGroups
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

    /**
     * Get the custom properties for this org unit
     */
    public function customProperties(): HasMany
    {
        return $this->hasMany(OrgUnitCustomProperty::class);
    }

    /**
     * Get the registration token for this org unit
     *
     * @return string|null The registration token
     */
    public function registrationToken(): ?string
    {
        $response = $this->getClient()->request(
            $this->getPath('/registration-token')
        );

        return $response['registrationToken'] ?? null;
    }

    /**
     * Get the active issues for this org unit
     */
    public function activeIssues(): HasMany
    {
        return $this->hasMany(ActiveIssue::class);
    }

    /**
     * Get the job statuses for this org unit
     */
    public function jobStatuses(): HasMany
    {
        return $this->hasMany(JobStatus::class);
    }

    /**
     * Get the user roles for this org unit
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Get the limits for this org unit
     */
    public function limits(): HasMany
    {
        return $this->hasMany(OrgUnitLimit::class);
    }

    /**
     * Get the custom property defaults for this org unit
     */
    public function customPropertyDefaults(): Collection
    {
        $response = $this->getClient()
            ->get($this->path.'/'.$this->getKey().'/org-custom-property-defaults');

        return new Collection($response['data'] ?? []);
    }

    /**
     * Get the users for this org unit
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the access groups for this org unit
     */
    public function accessGroups(): HasMany
    {
        return $this->hasMany(AccessGroup::class);
    }
}
