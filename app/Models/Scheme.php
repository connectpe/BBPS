<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scheme extends Model
{
    protected $fillable = ['scheme_name', 'is_active', 'updated_by'];

    /**
     * Relationship: Ek Scheme ke paas bahut saare Rules ho sakte hain.
     */
    public function rules(): HasMany
    {
        // Yahan 'scheme_id' foreign key hai jo scheme_rules table mein hai
        return $this->hasMany(SchemeRule::class, 'scheme_id', 'id');
    }
}