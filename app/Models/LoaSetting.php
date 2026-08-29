<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaSetting extends Model
{
    protected $fillable = [
        'signature_path',
    ];

    /**
     * The LoA signature has exactly one editable record, created on first use.
     */
    public static function current(): self
    {
        return static::firstOrCreate([]);
    }
}
