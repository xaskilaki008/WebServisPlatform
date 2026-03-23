<?php

use App\Models\Beach;
use Illuminate\Support\Facades\Route;

Route::get('/beaches', function () {
    return Beach::query()
        ->orderBy('number')
        ->get(['id', 'number', 'name', 'latitude', 'longitude', 'wave_level']);
});