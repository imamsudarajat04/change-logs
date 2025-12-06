<?php

namespace Imamsudarajat04\ChangeLogs\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Imamsudarajat04\ChangeLogs\Traits\HasChangeLogs;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasChangeLogs, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}