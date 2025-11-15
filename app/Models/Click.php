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
        'country',
        'city',
        'device',
        'browser',
        'os',
        'visitor_hash',
    ];

    // Relations

    public function trackedLink()
    {
        return $this->belongsTo(TrackedLink::class);
    }

    public function link()
    {
        // Accès direct au lien via la tracked_link
        return $this->hasOneThrough(
            Link::class,
            TrackedLink::class,
            'id',        // TrackedLink.id
            'id',        // Link.id
            'tracked_link_id', // Click.tracked_link_id
            'link_id'    // TrackedLink.link_id
        );
    }
}
