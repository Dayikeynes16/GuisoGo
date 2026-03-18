<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Promotion extends Model
{
    /** @use HasFactory<\Database\Factories\PromotionFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'price',
        'production_cost',
        'image_path',
        'is_active',
        'active_days',
        'starts_at',
        'ends_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'production_cost' => 'decimal:2',
            'is_active' => 'boolean',
            'active_days' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /** @var list<string> */
    protected $appends = ['image_url'];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image_path
                ? Storage::disk(config('filesystems.media_disk', 'public'))->url($this->image_path)
                : null,
        );
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function modifierGroups(): HasMany
    {
        return $this->hasMany(ModifierGroup::class)->orderBy('sort_order');
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = Carbon::now();

        if (! in_array($now->dayOfWeek, $this->active_days ?? [])) {
            return false;
        }

        if (! $this->starts_at || ! $this->ends_at) {
            return true;
        }

        $currentTime = $now->format('H:i');

        if ($this->starts_at > $this->ends_at) {
            return $currentTime >= $this->starts_at || $currentTime <= $this->ends_at;
        }

        return $currentTime >= $this->starts_at && $currentTime <= $this->ends_at;
    }
}
