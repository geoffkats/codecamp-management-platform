<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Support\SubmissionFile;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'max:500'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        return SubmissionFile::downloadResponse(
            $validated['path'],
            $validated['name'] ?? null
        );
    }
}
