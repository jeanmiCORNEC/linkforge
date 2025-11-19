<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateIntegration extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'pending', 'connected', 'error'];

    protected $fillable = [
        'user_id',
        'platform',
        'label',
        'status',
        'credentials',
        'last_synced_at',
        'last_error',
    ];

    protected $casts = [
        'credentials'    => 'array',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
