<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchemeRule extends Model
{
    protected $fillable = [
        'scheme_id', 'service_id', 'start_value', 'end_value', 
        'type', 'fee', 'min_fee', 'max_fee', 'is_active', 'updated_by'
    ];

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }
}