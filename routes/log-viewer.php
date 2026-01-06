<?php

use Illuminate\Support\Facades\Route;
use YuriZoom\MoonShineLogViewer\Controllers\LogViewerController;

$middleware = config('moonshine.auth.middleware');
$middleware = is_array($middleware) ? $middleware : [$middleware];

Route::group([
    'prefix' => config('moonshine.route.prefix'),
    'as' => 'moonshine.',
    'middleware' => [...$middleware, 'web'],
], function () {
    Route::get('logs/{file?}', [LogViewerController::class, 'index'])->name('log.viewer.file');
    Route::get('logs/{file}/tail', [LogViewerController::class, 'tail'])->name('log.viewer.tail');
});
