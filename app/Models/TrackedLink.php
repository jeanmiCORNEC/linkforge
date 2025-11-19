<?php

namespace App\Models;

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
    ];

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

    public function conversions()
    {
        return $this->hasMany(Conversion::class);
    }
}
