<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracked_link_id',
        'ip_address',
        'user_agent',
        'referrer',
    ];

    // Relations

    public function trackedLink()
    {
        return $this->belongsTo(TrackedLink::class);
    }
}
