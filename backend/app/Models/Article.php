<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Cache;

class Article extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'source_hash',
        'excerpt',
        'content',
        'image_url',
        'is_external',
        'external_url',
        'status',
        'published_at',
        'metadata',
    ];

    protected $casts = [
        'is_external' => 'boolean',
        'published_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $dates = [
        'published_at',
    ];

    protected static function booted()
    {
        static::created(function ($article) {
            Cache::flush();
        });
        
        static::updated(function ($article) {
            Cache::flush();
        });
        
        static::deleted(function ($article) {
            Cache::flush();
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeExternal($query)
    {
        return $query->where('is_external', true);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_external', false);
    }
}
