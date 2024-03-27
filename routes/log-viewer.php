<?php

use Illuminate\Support\Facades\Route;
use MoonShine\LogViewer\Controllers\LogViewerController;

Route::group([
    'prefix' => 'moonshine',
    'as' => 'moonshine.',
    //'middleware' => config('moonshine.auth.middleware'),
], function () {
    Route::get('logs/{file?}', [LogViewerController::class, 'index'])->name('log.viewer.file');
    Route::get('logs/{file}/tail', [LogViewerController::class, 'tail'])->name('log.viewer.tail');
});
