<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

if (!function_exists('uploadFile')) {
    /**
     * Upload a single or multiple files, delete old file(s), and store in a folder on 'public' disk
     *
     * @param UploadedFile|array|null $files       New file(s) from request
     * @param string|array|null $oldFiles          Old file(s) to delete
     * @param string $folder                        Folder name under public disk
     * @return string|array|null                    New file path(s)
     */
    function uploadFile($files, $folder = 'uploads', $oldFiles = null)
    {
        // Delete old file(s)
        if ($oldFiles) {
            if (is_array($oldFiles)) {
                foreach ($oldFiles as $file) {
                    if ($file && Storage::disk('public')->exists($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            } else {
                if (Storage::disk('public')->exists($oldFiles)) {
                    Storage::disk('public')->delete($oldFiles);
                }
            }
        }

        // Nothing to upload
        if (!$files) return null;

        // Handle multiple files
        if (is_array($files)) {
            $paths = [];
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $paths[] = $file->store($folder, 'public'); // same as your code
                }
            }
            return $paths;
        }

        // Single file
        if ($files instanceof UploadedFile) {
            return $files->store($folder, 'public'); // same as your code
        }

        return null;
    }
}



if (!function_exists('getFilePath')) {
    /**
     * Get public URL for stored file(s)
     *
     * @param string|array|null $files   Stored DB path(s)
     * @return string|array|null
     */
    function getFilePath($files)
    {

        if (!$files) {
            return null;
        }

        // Multiple files
        if (is_array($files)) {
            return array_map(function ($file) {
                return asset('storage/' . ltrim($file, '/'));
            }, $files);
        }

        // Single file
        return asset('storage/' . ltrim($files, '/'));
    }
}
