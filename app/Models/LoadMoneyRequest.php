<?php

namespace App\Models;

use App\Facades\FileUpload;
use Illuminate\Database\Eloquent\Model;

class LoadMoneyRequest extends Model
{
    protected $fillable = ['user_id', 'request_id', 'amount', 'utr_no', 'image_url', 'status', 'request_time', 'remark', 'updated_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function getImageUrlAttribute($value)
    {
        if (! $value) {
            return null;
        }
        return FileUpload::getFilePath($value);
    }
}
