<?php

namespace Tests\Unit\Concerns\Stubs;

use Spinen\Ncentral\Concerns\HasNcentral;

class User
{
    use HasNcentral;

    public $attributes = [
        'ncentral_token' => 'encrypted',
    ];

    public $fillable = [];

    public $hidden = [];

    /**
     * @var string
     */
    protected $ncentral_token = 'pk_token';

    public function getBuilder()
    {
        return $this->builder;
    }
}
