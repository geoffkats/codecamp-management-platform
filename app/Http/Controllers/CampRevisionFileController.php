<?php

namespace App\Http\Controllers;

use App\Models\CampContentRevisionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampRevisionFileController extends Controller
{
    public function __invoke(Request $request, CampContentRevisionFile $file): StreamedResponse
    {
        abort_unless($file->revision?->canComment($request->user()), 403);
        abort_unless(Storage::disk($file->disk)->exists($file->path), 404);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
}
