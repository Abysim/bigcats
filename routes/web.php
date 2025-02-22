<?php

use Illuminate\Support\Facades\Route;
use Spatie\Feed\Http\FeedController;

Route::get('news.xml',  FeedController::class)->name("feeds.news");
