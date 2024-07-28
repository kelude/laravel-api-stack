<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return [
        'version' => app()->version(),
        'locale' => $request->getPreferredLanguage(),
        'time' => now(),
    ];
});
