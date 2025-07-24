<?php

namespace Eclipse\Catalogue\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxClass extends Model
{
    use SoftDeletes;

    protected $table = 'pim_tax_classes';

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public function getFillable(): array
    {
        $fillable = [
            'rate',
            'is_default',
        ];

        if (config('eclipse-catalogue.tenancy.foreign_key')) {
            $fillable[] = config('eclipse-catalogue.tenancy.foreign_key');
        }

        return $fillable;
    }
}
