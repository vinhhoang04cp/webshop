<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $table = 'user_roles';

    protected $primaryKey = ['user_id', 'role_id'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];
}
