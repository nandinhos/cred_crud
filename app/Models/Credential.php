<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Credential extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'fscs',
        'name',
        'secrecy',
        'credential',
        'concession',
        'validity',
    ];
}
