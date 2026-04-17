<?php

namespace App\Models;

use App\Enums\StopType;
use Database\Factories\StopFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Stop extends Model implements HasMedia
{
    /** @use HasFactory<StopFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'latitude',
        'longitude',
        'trail_order',
        'type',
        'is_published',
    ];

    protected $casts = [
        'type' => StopType::class,
        'is_published' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')->useDisk('public')->singleFile()->acceptsMimeTypes(['image/svg+xml', 'image/png', 'image/webp']);
        $this->addMediaCollection('photo')->useDisk('public')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
        $this->addMediaCollection('audio')->useDisk('public')->acceptsMimeTypes(['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp4']);
        $this->addMediaCollection('video')->useDisk('public')->acceptsMimeTypes(['video/mp4', 'video/webm', 'video/ogg']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
