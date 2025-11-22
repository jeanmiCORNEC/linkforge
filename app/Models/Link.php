<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Link extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'destination_url',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations

    public function user()
    {
        return $this->belongsTo(User::class);
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
            'link_id',       // clé étrangère sur tracked_links
            'tracked_link_id', // clé étrangère sur clicks
            'id',            // clé locale sur links
            'id'             // clé locale sur tracked_links
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($link) {
            foreach ($link->trackedLinks as $trackedLink) {
                \Illuminate\Support\Facades\Cache::forget("link_redirect_{$trackedLink->short_code}");
                \Illuminate\Support\Facades\Cache::forget("link_redirect_{$trackedLink->tracking_key}");
            }
        });

        static::deleted(function ($link) {
            foreach ($link->trackedLinks as $trackedLink) {
                \Illuminate\Support\Facades\Cache::forget("link_redirect_{$trackedLink->short_code}");
                \Illuminate\Support\Facades\Cache::forget("link_redirect_{$trackedLink->tracking_key}");
            }
        });
    }
}
