<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    dd(env('AUVO_API_KEY_INSPECTION'));
});
