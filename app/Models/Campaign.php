<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'status',
        'notes',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    // Relations

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sources()
    {
        return $this->hasMany(Source::class);
    }

    public function clicks()
    {
        return $this->hasMany(Click::class);
    }
}
