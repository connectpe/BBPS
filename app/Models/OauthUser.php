<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OauthUser extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'client_id',
        'client_secret',
        'is_active',
        
    ];

    public $timestamps = true;

    public function globalServices()
    {
        return $this->hasOne(GlobalService::class, 'id', 'service_id');
    }

    /**
     * Verify client secret
     * 
     * @param string $plainSecret
     * @return bool
     */
    public function verifyClientSecret($plainSecret)
    {
        try {
            $decryptedSecret = decrypt($this->client_secret);
            return $decryptedSecret === $plainSecret;
        } catch (\Exception $e) {
            return false;
        }
    }
}
