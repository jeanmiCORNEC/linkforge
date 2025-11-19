<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Source extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'name',
        'platform',
        'external_id',
        'notes',
    ];

    // Relations

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function trackedLinks()
    {
        return $this->hasMany(TrackedLink::class);
    }

    public function clicks()
    {
        return $this->hasManyThrough(
            Click::class,
            TrackedLink::class,
            'source_id',        // FK sur tracked_links
            'tracked_link_id',  // FK sur clicks
            'id',               // PK local sur sources
            'id'                // PK local sur tracked_links
        );
    }

    public function conversions()
    {
        return $this->hasMany(Conversion::class);
    }
}
