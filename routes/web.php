<?php

use App\Enums\ErrorCode;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', fn () => ErrorCode::UNAUTHORIZED->response())->name('login');
