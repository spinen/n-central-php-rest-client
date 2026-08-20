<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class AccessGroup
 *
 * @property int $groupId
 * @property int $orgUnitId
 * @property string $groupName
 * @property string $groupDescription
 * @property array $orgUnitIds
 * @property array $deviceIds
 * @property array $userIds
 * @property bool $autoIncludeNewOrgUnits
 */
class AccessGroup extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'groupId' => 'int',
        'orgUnitId' => 'int',
        'orgUnitIds' => 'array',
        'deviceIds' => 'array',
        'userIds' => 'array',
        'autoIncludeNewOrgUnits' => 'bool',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'groupId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/access-groups';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = true;
}
