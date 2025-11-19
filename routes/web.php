<?php

use Illuminate\Support\Facades\File;

Route::get('/unauthorized', function () {
    return view('error.401');
})->name('unauthorized');

// Serve storage files if symlink doesn't work (fallback route)
// This route will only be hit if the file doesn't exist in public/storage symlink
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    // Security: prevent directory traversal
    $realPath = realpath($filePath);
    $realStoragePath = realpath(storage_path('app/public'));
    
    if (!$realPath || strpos($realPath, $realStoragePath) !== 0) {
        abort(404);
    }
    
    if (!File::exists($filePath)) {
        abort(404);
    }
    
    $file = File::get($filePath);
    $type = File::mimeType($filePath);
    
    return response($file, 200)
        ->header('Content-Type', $type)
        ->header('Content-Length', File::size($filePath));
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