<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class SoftwareInstaller
 *
 * @property int $softwareId
 * @property string $softwareName
 * @property string $description
 * @property string $installerType
 * @property string $operatingSystem
 * @property string $softwareType
 * @property string $version
 */
class SoftwareInstaller extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'softwareId' => 'int',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'softwareId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/software/installers';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;
}
