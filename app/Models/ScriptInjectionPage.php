<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScriptInjectionPage extends Model
{
    protected $table = 'script_injection_page';

    public $timestamps = false;

    protected $fillable = [
        'script_injection_id',
        'page_type',
    ];

    public function scriptInjection(): BelongsTo
    {
        return $this->belongsTo(ScriptInjection::class);
    }
}
