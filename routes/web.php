<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackgroundJobController;

Route::get('/', function () {
    return view('welcome');
});

// admin: routes related to custom background jobs runner
Route::prefix('admin')->group(function () {
    Route::get('/background-jobs', [BackgroundJobController::class, 'index'])->name('admin.background-jobs.index');
    Route::post('/background-jobs/cancel/{id}', [BackgroundJobController::class, 'cancel']);
});
