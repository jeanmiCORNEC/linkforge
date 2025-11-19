<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversion extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'approved', 'rejected', 'void'];

    protected $fillable = [
        'user_id',
        'tracked_link_id',
        'link_id',
        'source_id',
        'campaign_id',
        'order_id',
        'status',
        'currency',
        'revenue',
        'commission',
        'visitor_hash',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'revenue'  => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trackedLink()
    {
        return $this->belongsTo(TrackedLink::class);
    }

    public function link()
    {
        return $this->belongsTo(Link::class);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
