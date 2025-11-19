<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

Route::get('/unauthorized', function () {
    return view('error.401');
})->name('unauthorized');

// Serve storage files if symlink doesn't work (fallback route)
// This route will only be hit if the file doesn't exist in public/storage symlink
Route::get('/storage/{path}', function ($path) {
    // Remove any leading slashes
    $path = ltrim($path, '/');
    
    // Security: prevent directory traversal
    if (str_contains($path, '..') || str_contains($path, "\0")) {
        abort(404);
    }
    
    $filePath = storage_path('app/public/' . $path);
    
    // Get real paths for security check
    $realPath = realpath($filePath);
    $realStoragePath = realpath(storage_path('app/public'));
    
    // Security: ensure file is within storage/app/public directory
    if (!$realPath || !$realStoragePath || strpos($realPath, $realStoragePath) !== 0) {
        abort(404);
    }
    
    if (!File::exists($filePath) || !File::isFile($filePath)) {
        abort(404);
    }
    
    try {
        $file = File::get($filePath);
        $type = File::mimeType($filePath) ?: 'application/octet-stream';
        $size = File::size($filePath);
        
        return response($file, 200)
            ->header('Content-Type', $type)
            ->header('Content-Length', $size)
            ->header('Cache-Control', 'public, max-age=31536000')
            ->header('Accept-Ranges', 'bytes');
    } catch (\Exception $e) {
        \Log::error('Storage route error: ' . $e->getMessage(), [
            'path' => $path,
            'filePath' => $filePath
        ]);
        abort(404);
    }
})->where('path', '.*');





foreach (glob(__DIR__ . '/page/*.php') as $routeFile) {
    require $routeFile;
}

foreach (glob(__DIR__ . '/admin-auth/*.php') as $routeFile) {
    require $routeFile;
}


Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    foreach (glob(__DIR__ . '/admin/*.php') as $routeFile) {
        require $routeFile;
    }
});

Route::fallback(function (Request $request) {
    return response()->view('errors.404', [], 404);
});



// Route::middleware('auth:investor')->name('investor.')->group(function () {
//     foreach (glob(__DIR__ . '/investor/*.php') as $routeFile) {
//         require $routeFile;
//     }
// });