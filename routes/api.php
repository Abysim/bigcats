<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->post('/articles/create', [ArticleController::class, 'create']);
Route::middleware('auth:sanctum')->post('/news/create', [NewsController::class, 'create']);
Route::middleware('auth:sanctum')->post('/photos/create', [PhotoController::class, 'create']);
