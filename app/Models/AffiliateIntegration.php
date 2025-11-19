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
    ];

    protected $casts = [
        'credentials' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
