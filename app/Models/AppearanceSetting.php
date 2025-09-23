<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppearanceSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'logo_updated_at' => 'datetime',
        'login_logo_updated_at' => 'datetime',
        'favicon_updated_at' => 'datetime',
        'qr_updated_at' => 'datetime',
    ];
}
