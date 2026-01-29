<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class FileUpload extends Facade
{
    /**
     * Return the class that the Facade should resolve.
     */
    protected static function getFacadeAccessor()
    {
        return \App\Helpers\FileUploadHelper::class;
    }
}
