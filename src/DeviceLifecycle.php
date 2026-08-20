<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Model;

/**
 * Class DeviceLifecycle
 *
 * Contains lifecycle information about a device including warranty,
 * lease, and purchase details.
 *
 * @property string $warrantyExpiryDate
 * @property string $leaseExpiryDate
 * @property string $expectedReplacementDate
 * @property string $purchaseDate
 * @property float $cost
 * @property string $location
 * @property string $assetTag
 * @property string $description
 * @property string $updateWarrantyError
 * @property string $lastSystemWarrantyDiscovery
 * @property string $lastDiscovery
 */
class DeviceLifecycle extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'cost' => 'float',
    ];

    /**
     * Is the response a collection of items?
     */
    public bool $collection = false;

    /**
     * Path to API endpoint.
     */
    protected string $path = '/assets/lifecycle';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;
}
