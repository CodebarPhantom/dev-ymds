<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;


class UploadsController extends Controller
{
    public function __invoke(Request $request, $path)
    {
        // Get the full file path on the server
        $filePath = Storage::disk('uploads')->path($path);

        // Check if the file exists and is a valid file (not a directory)
        if (!Storage::disk('uploads')->exists($path) || !is_file($filePath)) {
            // Return a custom response for directories or non-existing paths
            abort(404, "The file doesn't exist or the path is a directory.");
        }

        // Return the file as a response if it's a valid file
        return Storage::disk('uploads')->response($path);
    }
}
