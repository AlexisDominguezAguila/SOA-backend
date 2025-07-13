<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/probar-api-key', function () {
    $response = Http::withToken(env('OPENAI_API_KEY'))
        ->get('https://api.openai.com/v1/models');

    return $response->json();
});
