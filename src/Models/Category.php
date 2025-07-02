<?php

namespace Eclipse\Catalogue\Models;

use Eclipse\Core\Models\Site;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'catalogue_categories';

    protected $fillable = [
        'name',
        'parent_id',
        'image',
        'sort',
        'is_active',
        'code',
        'recursive_browsing',
        'sef_key',
        'short_desc',
        'description',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'recursive_browsing' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $category) {
            $category->site_id = Filament::getTenant()->id;
        });
    }
}
