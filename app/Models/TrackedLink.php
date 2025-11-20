<?php

namespace App\Models;

use App\Support\Links\ShortCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackedLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'link_id',
        'source_id',
        'tracking_key',
        'short_code',
    ];

    protected static function booted(): void
    {
        static::created(function (TrackedLink $trackedLink) {
            if ($trackedLink->short_code) {
                return;
            }

            $trackedLink->short_code = ShortCode::encode($trackedLink->id);
            // Extra save only happens once to backfill older entries and factory-created rows.
            $trackedLink->save();
        });
    }

    // Relations

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function link()
    {
        return $this->belongsTo(Link::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function clicks()
    {
        return $this->hasMany(Click::class);
    }

}
