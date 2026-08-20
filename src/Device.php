<?php

namespace Spinen\Ncentral;

use Spinen\Ncentral\Support\Collection;
use Spinen\Ncentral\Support\Model;
use Spinen\Ncentral\Support\Relations\BelongsTo;
use Spinen\Ncentral\Support\Relations\HasMany;

/**
 * Class Device
 *
 * @property bool $isProbe
 * @property bool $stillLoggedIn
 * @property int $customerId
 * @property int $deviceId
 * @property string $customerName
 * @property string $description
 * @property string $deviceClass
 * @property string $deviceClassLabel
 * @property string $discoveredName
 * @property string $lastLoggedInUser
 * @property string $licenseMode
 * @property string $longName
 * @property string $osId
 * @property string $remoteControlUri
 * @property string $siteName
 * @property string $soName
 * @property string $sourceUri
 * @property string $supportedOS
 * @property string $supportedOSLabel
 * @property string $uri
 * @property-read Customer $customer
 * @property-read Collection|DeviceCustomProperty[] $customProperties
 * @property-read Collection|DeviceNote[] $notes
 * @property-read DeviceAsset $asset
 * @property-read DeviceLifecycle $lifecycle
 * @property-read Collection|MaintenanceWindow[] $maintenanceWindows
 * @property-read Collection|ServiceMonitorStatus[] $serviceMonitorStatus
 * @property-read RemoteControl $remoteControl
 * @property-read Collection|DeviceTask[] $tasks
 */
class Device extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'customerId' => 'int',
        'deviceId' => 'int',
        'isProbe' => 'bool',
        'stillLoggedIn' => 'bool',
    ];

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'deviceId';

    /**
     * Path to API endpoint.
     */
    protected string $path = '/devices';

    /**
     * Is the model readonly?
     */
    protected bool $readonlyModel = false;

    /**
     * Get the customer that owns this device
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customerId');
    }

    /**
     * Get the custom properties for this device
     */
    public function customProperties(): HasMany
    {
        return $this->hasMany(DeviceCustomProperty::class);
    }

    /**
     * Get the notes for this device
     */
    public function notes(): HasMany
    {
        return $this->hasMany(DeviceNote::class);
    }

    /**
     * Get the asset information for this device
     */
    public function asset(): HasMany
    {
        return $this->hasMany(DeviceAsset::class);
    }

    /**
     * Get the lifecycle information for this device
     */
    public function lifecycle(): HasMany
    {
        return $this->hasMany(DeviceLifecycle::class);
    }

    /**
     * Get the maintenance windows for this device
     */
    public function maintenanceWindows(): HasMany
    {
        return $this->hasMany(MaintenanceWindow::class);
    }

    /**
     * Get the service monitor status for this device
     */
    public function serviceMonitorStatus(): HasMany
    {
        return $this->hasMany(ServiceMonitorStatus::class);
    }

    /**
     * Get the remote control info for this device
     */
    public function remoteControl(): HasMany
    {
        return $this->hasMany(RemoteControl::class);
    }

    /**
     * Get the scheduled tasks for this device
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(DeviceTask::class);
    }

    /**
     * Get the activation key for this device
     */
    public function activationKey(): ?string
    {
        $response = $this->getClient()
            ->get($this->path.'/'.$this->getKey().'/activation-key');

        return $response['activationKey'] ?? null;
    }
}
