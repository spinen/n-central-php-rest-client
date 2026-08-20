<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class UserRole
 *
 * @property int $roleId
 * @property int $orgUnitId
 * @property string $roleName
 * @property string $roleDescription
 * @property array $userIds
 */
class UserRole extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'roleId' => 'int',
        'orgUnitId' => 'int',
        'userIds' => 'array',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'roleId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/user-roles';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;
}
