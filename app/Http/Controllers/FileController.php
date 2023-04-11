<?php

namespace App\Http\Controllers;

use Error;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function uploadSingleFile(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file',
        ]);

        try {
            $path =  $this->uploadToS3($request->file('file'));
            return $this->respondSuccess(['file' => $path], 'File uploaded successfully');
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->respondError('Error occurred while uploading documents');
        }
    }

    public function uploadMultipleFiles(Request $request)
    {
        $this->validate($request, [
            'files' => ['required', 'array'],
            'files.*' => 'required|file',
        ]);

        try {
            $paths = [];

            $files = $request->file('files');
            foreach ($files as $key => $file) {
                $paths[] =  $this->uploadToS3($file);
            }
            return $this->respondSuccess(['files' => $paths], 'File uploaded successfully');
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->respondError('Error occurred while uploading documents');
        }
    }


    public function  uploadToS3(UploadedFile $image, $directory = 'general')
    {

        try {
            $s3 = Storage::disk('s3');
            $imageFileName = time() . '.' . $image->getClientOriginalExtension();
            $pathToImage = 'public/storage/' . $directory . '/' . $imageFileName;

            $resp = $s3->put($pathToImage, file_get_contents($image), 'public');
            if (!$resp) {
                throw new Error('Error occurred while uploading the file');
            }
            return Storage::disk('s3')->url($pathToImage);
        } catch (\Throwable $th) {
            Log::error($th);
            throw new Error('Error occurred while uploading the file');
        }
    }
}
