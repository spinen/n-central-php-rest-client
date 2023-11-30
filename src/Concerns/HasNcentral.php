<?php

namespace Spinen\Ncentral\Concerns;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Crypt;
use Spinen\Ncentral\Api\Client as Ncentral;
use Spinen\Ncentral\Api\Token;
use Spinen\Ncentral\Exceptions\NoClientException;
use Spinen\Ncentral\Support\Builder;

/**
 * Trait HasNcentral
 *
 * @property Ncentral $ncentral
 * @property string $ncentral_token
 */
trait HasNcentral
{
    /**
     * Ncentral Builder instance
     */
    protected ?Builder $builder = null;

    /**
     * Return cached version of the Ncentral Builder for the user
     *
     * @throws BindingResolutionException
     */
    public function ncentral(): Builder
    {
        // TODO: Need to deal with null ncentral_token
        if (is_null($this->builder)) {
            $this->builder = Container::getInstance()
                ->make(Builder::class)
                ->setClient(
                    Container::getInstance()
                        ->make(Ncentral::class)
                        ->setToken($this->ncentral_token)
                );
        }

        return $this->builder;
    }

    /**
     * Accessor for Ncentral Client.
     *
     * @throws BindingResolutionException
     * @throws NoClientException
     */
    public function getNcentralAttribute(): Ncentral
    {
        return $this->ncentral()
            ->getClient();
    }

    /**
     * Accessor/Mutator for ncentralToken.
     */
    public function ncentralToken(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes): ?Token => ! is_null($attributes['ncentral_token'])
              ? unserialize(Crypt::decryptString($attributes['ncentral_token']))
              : null,
            set: function ($value): ?string {
                // If setting the password & already have a client, then
                // empty the client to use new password in client
                if (! is_null($this->builder)) {
                    $this->builder = null;
                }

                return is_null($value)
                    ? null
                    : Crypt::encryptString(serialize($value));
            },
        );
    }

    /**
     * Make sure that the ncentral_token is fillable & protected
     */
    public function initializeHasNcentral(): void
    {
        $this->fillable[] = 'ncentral_token';
        $this->hidden[] = 'ncentral';
        $this->hidden[] = 'ncentral_token';
    }
}
