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
            'file' => ['required', 'file', 'max:512'],
            'name' => ['required', 'string'],
        ]);

        try {
            $path =  $this->uploadToS3($request->file('file'));
            return $this->respondSuccess(['file' => ['url' => $path, 'name' => $request->input('name')]], 'File uploaded successfully');
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->respondError('Error occurred while uploading documents');
        }
    }

    public function uploadMultipleFiles(Request $request)
    {
        $this->validate($request, [
            'documents' => ['required', 'array'],
            'documents.*.file' => ['required', 'file', 'max:512'],
            'documents.*.name' => ['required', 'string'],
        ]);

        try {
            $uploads = [];

            $documents = $request->documents;
            foreach ($documents as $key => $item) {
                $uploads[] = ['url' =>  $this->uploadToS3($item['file']), 'name' => $item['name']];
            }
            return $this->respondSuccess(['files' => $uploads], 'File uploaded successfully');
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
