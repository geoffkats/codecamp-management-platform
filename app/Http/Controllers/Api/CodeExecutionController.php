<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CodeExecutionController extends Controller
{
    /**
     * Execute Python code
     */
    public function executePython(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10000',
        ]);

        $code = $request->input('code');
        
        // Security: Check for dangerous operations
        $dangerousPatterns = [
            'import os',
            'import sys',
            'import subprocess',
            '__import__',
            'eval(',
            'exec(',
            'compile(',
            'open(',
            'file(',
            'input(',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (stripos($code, $pattern) !== false) {
                return response()->json([
                    'success' => false,
                    'error' => 'Security Error: Restricted operation detected. Cannot use: ' . $pattern,
                ], 400);
            }
        }

        try {
            // Create temporary file
            $filename = 'python_' . Str::random(16) . '.py';
            $filepath = storage_path('app/temp/' . $filename);
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            // Write code to file
            file_put_contents($filepath, $code);
            
            // Execute with timeout (5 seconds)
            $result = Process::timeout(5)->run("python {$filepath}");
            
            // Clean up
            @unlink($filepath);
            
            if ($result->successful()) {
                return response()->json([
                    'success' => true,
                    'output' => $result->output() ?: 'Code executed successfully!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result->errorOutput() ?: 'Execution failed',
                ], 400);
            }
            
        } catch (\Exception $e) {
            // Clean up on error
            if (isset($filepath) && file_exists($filepath)) {
                @unlink($filepath);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Execution error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute JavaScript code (server-side with Node.js)
     */
    public function executeJavaScript(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10000',
        ]);

        $code = $request->input('code');
        
        // Security checks
        $dangerousPatterns = [
            'require(',
            'import ',
            'process.',
            'child_process',
            'fs.',
            'eval(',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (stripos($code, $pattern) !== false) {
                return response()->json([
                    'success' => false,
                    'error' => 'Security Error: Restricted operation detected.',
                ], 400);
            }
        }

        try {
            // Create temporary file
            $filename = 'js_' . Str::random(16) . '.js';
            $filepath = storage_path('app/temp/' . $filename);
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            // Write code to file
            file_put_contents($filepath, $code);
            
            // Execute with Node.js (timeout: 5 seconds)
            $result = Process::timeout(5)->run("node {$filepath}");
            
            // Clean up
            @unlink($filepath);
            
            if ($result->successful()) {
                return response()->json([
                    'success' => true,
                    'output' => $result->output() ?: 'Code executed successfully!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result->errorOutput() ?: 'Execution failed',
                ], 400);
            }
            
        } catch (\Exception $e) {
            // Clean up on error
            if (isset($filepath) && file_exists($filepath)) {
                @unlink($filepath);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Execution error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
