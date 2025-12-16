<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/status', function () {
    return response()->json([
        'app' => 'TraderApp API',
        'status' => 'running',
        'version' => '1.0.0'
    ]);
});
