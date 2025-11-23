<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        try {
            $image = $request->file('image');
            
            // Generate unique filename
            $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
            
            // Store in public disk under lesson-images folder
            $path = $image->storeAs('lesson-images', $filename, 'public');
            
            // Return the public URL
            $url = Storage::url($path);
            
            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $path,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
