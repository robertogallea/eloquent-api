<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::get('/eloquent_api_forget/{id?}', function (Request $request, $id) {
    File::delete(config('sushi.cache-path').'/'.$id.'.sqlite');

    return response()->noContent();
});