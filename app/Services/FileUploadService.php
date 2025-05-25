<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FileUploadService
{
    public function upload(string $disk, string $path, $file, array $validations = [])
    {
        $this->validateFile($file, $validations);

        $originalName = $file->getClientOriginalName();
        $newName = uniqid('', true) . '.' . $file->getClientOriginalExtension();

        $uploaded = Storage::disk($disk)->putFileAs($path, $file, $newName);

        if (!$uploaded) {
            throw new \RuntimeException("Failed to upload file.");
        }

        return [
            'original_name' => $originalName,
            'filename' => $newName,
            'path' => Storage::disk($disk)->path($path . '/' . $newName),
        ];
    }

    private function validateFile($file, array $validations)
    {
        $validator = \Validator::make(['file' => $file], $validations + [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
