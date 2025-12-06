<?php

namespace Imamsudarajat04\ChangeLogs\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Imamsudarajat04\ChangeLogs\Traits\HasChangeLogs;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasChangeLogs, HasUuids, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'status',
    ];
}